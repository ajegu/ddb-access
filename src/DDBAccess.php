<?php

namespace Ajegu\DdbAccess;

use Ajegu\DdbAccess\Adapter\DynamoDbAdapter;
use Ajegu\DdbAccess\Adapter\MarshalerAdapter;
use Ajegu\DdbAccess\Contract\DDBAccessInterface;
use Ajegu\DdbAccess\Contract\ItemBuilderInterface;
use Ajegu\DdbAccess\Contract\QueryInterface;
use Ajegu\DdbAccess\Contract\ResultInterface;
use Ajegu\DdbAccess\Contract\TableDefinitionInterface;
use Ajegu\DdbAccess\Exception\DDBAccessException;
use Ajegu\DdbAccess\Model\Direction;
use Ajegu\DdbAccess\Model\Event;
use Ajegu\DdbAccess\Model\Result;
use Ajegu\DdbAccess\Service\CursorService;
use Ajegu\DdbAccess\Service\EventService;
use Psr\Log\LoggerInterface;

class DDBAccess implements DDBAccessInterface
{
    private CursorService $cursorService;
    private EventService $eventService;

    /**
     * @param DynamoDbAdapter $dynamoDbAdapter
     * @param MarshalerAdapter $marshalerAdapter
     * @param LoggerInterface $logger
     * @param TableDefinitionInterface $tableDefinition
     * @param iterable $builders
     */
    public function __construct(
        private DynamoDbAdapter  $dynamoDbAdapter,
        private MarshalerAdapter $marshalerAdapter,
        private LoggerInterface  $logger,
        private TableDefinitionInterface $tableDefinition,
        private iterable         $builders = [],
    )
    {
        $this->cursorService = new CursorService(
            $this->logger,
            $this->tableDefinition->getPartitionKey(),
            $this->tableDefinition->getSortKey()
        );

        $this->eventService = new EventService(
            $this->builders
        );
    }

    /**
     * @param QueryInterface $query
     * @return ResultInterface
     * @throws DDBAccessException
     */
    public function findAll(QueryInterface $query): ResultInterface
    {
        $args = [
            'TableName' => $this->tableDefinition->getTable(),
            'KeyConditionExpression' => '#partitionKeyName = :partitionKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $this->marshalerAdapter->marshalValue($query->getPartitionKey())
            ],
            'ExpressionAttributeNames' => [
                '#partitionKeyName' => $this->tableDefinition->getPartitionKey()
            ]
        ];

        if ($sortKeyValue = $query->getSortKey()) {
            $args['KeyConditionExpression'] .= ' AND begins_with(#sortKeyName, :sortKeyValue)';
            $args['ExpressionAttributeValues'][':sortKeyValue'] = $this->marshalerAdapter->marshalValue($sortKeyValue);
            $args['ExpressionAttributeNames']['#sortKeyName'] = $this->tableDefinition->getSortKey();
        }

        if ($query->getDirection() === Direction::DESC) {
            $args['ScanIndexForward'] = false;
        }

        if ($queryCursor = $query->getCursor()) {
            $cursorValue = $this->cursorService->decode($queryCursor);
            $args['ExclusiveStartKey'] = $cursorValue->getKeys();
            $args['ScanIndexForward'] = $cursorValue->getDirection() === Direction::ASC;
        }

        if ($pageSize = $query->getPageSize()) {
            $args['Limit'] = $pageSize;
        }

        // Call DynamoDb
        $items = [];
        $stop = false;

        // We must do an extra query to see if we are more items instead of DynamoDB return a last evaluated key
        do {
            $result = $this->dynamoDbAdapter->query($args);

            foreach ($result->get('Items') as $item) {
                $items[] = $item;
                if (count($items) === $query->getPageSize()) {
                    $stop = true;
                    break;
                }
            }

            if ($lastEvaluatedKey = $result->get('LastEvaluatedKey')) {
                $args['ExclusiveStartKey'] = $lastEvaluatedKey;
            }
        } while ($lastEvaluatedKey && !$stop);

        // We must evaluate the last key to know if we are more items
        if ($lastEvaluatedKey) {
            $args['ExclusiveStartKey'] = $lastEvaluatedKey;
            $args['Limit'] = 1;

            $check = $this->dynamoDbAdapter->query($args);
            if ($check->get('Count') === 0) {
                $lastEvaluatedKey = null;
            }
        }

        // Build the cursor
        $cursor = $this->cursorService->build($items, $query, $lastEvaluatedKey);

        // Unmarshal the result items
        $items = array_map(function (array $item) {
            $item = $this->marshalerAdapter->unmarshalItem($item);

            return $this->eventService->dispatch(
                $item,
                $item[$this->tableDefinition->getPartitionKey()],
                $item[$this->tableDefinition->getSortKey()],
                Event::AFTER_QUERY
            );
        }, $items);

        // Order the items by sort key and direction
        usort($items, function ($a, $b) use ($query) {
            $sortKey = $this->tableDefinition->getSortKey();
            if ($query->getDirection() === Direction::ASC) {
                return $a[$sortKey] > $b[$sortKey];
            }

            return $a[$sortKey] < $b[$sortKey];
        });

        return new Result($items, $cursor);
    }

    /**
     * @param string $partitionKeyValue
     * @param string $sortKeyValue
     * @param array $item
     * @return void
     * @throws DDBAccessException
     */
    public function save(string $partitionKeyValue, string $sortKeyValue, array $item): void
    {
        $item = $this->eventService->dispatch($item, $partitionKeyValue, $sortKeyValue, Event::BEFORE_SAVE);
        $item[$this->tableDefinition->getPartitionKey()] = $partitionKeyValue;
        $item[$this->tableDefinition->getSortKey()] = $sortKeyValue;

        $args = [
            'TableName' => $this->tableDefinition->getTable(),
            'Item' => $this->marshalerAdapter->marshalItem($item)
        ];

        $this->dynamoDbAdapter->putItem($args);
    }
}
