<?php

namespace Ajegu\DdbAccess;

use Ajegu\DdbAccess\Adapter\DynamoDbAdapter;
use Ajegu\DdbAccess\Adapter\MarshalerAdapter;
use Ajegu\DdbAccess\Contract\DDBAccessInterface;
use Ajegu\DdbAccess\Contract\ItemBuilderInterface;
use Ajegu\DdbAccess\Contract\QueryInterface;
use Ajegu\DdbAccess\Contract\ResultInterface;
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
     * @param string $tableName DynamoDb table name
     * @param string $partitionKeyName DynamoDb partition key name
     * @param string $sortKeyName DynamoDb partition key name
     * @param ItemBuilderInterface[] $builders
     */
    public function __construct(
        private DynamoDbAdapter  $dynamoDbAdapter,
        private MarshalerAdapter $marshalerAdapter,
        private LoggerInterface  $logger,
        private string           $tableName,
        private string           $partitionKeyName,
        private string           $sortKeyName,
        private iterable         $builders = [],
    )
    {
        $this->cursorService = new CursorService(
            $this->logger,
            $this->partitionKeyName,
            $this->sortKeyName
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
        $partitionKeyMarshalled = $this->marshalerAdapter->marshalValue($query->getPartitionKeyValue());

        $args = [
            'TableName' => $this->tableName,
            'KeyConditionExpression' => $this->partitionKeyName . ' = :partitionKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyMarshalled
            ],
            'ExpressionAttributeNames' => [
                '#partitionKeyName' => $this->partitionKeyName
            ]
        ];

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
                if (count($items) === $query->getPageSize()) {
                    $stop = true;
                    break;
                }
                $items[] = $item;
            }

            if ($lastEvaluatedKey = $result->get('LastEvaluatedKey')) {
                $args['ExclusiveStartKey'] = $lastEvaluatedKey;
            }
        } while ($lastEvaluatedKey && !$stop);


        // Build the cursor
        $cursor = $this->cursorService->build($items, $query, $lastEvaluatedKey);

        // Unmarshal the result items
        $items = array_map(function (array $item) {
            $item = $this->marshalerAdapter->unmarshalItem($item);

            return $this->eventService->dispatch(
                $item,
                $item[$this->partitionKeyName],
                $item[$this->sortKeyName],
                Event::AFTER_QUERY
            );
        }, $items);

        // Order the items by sort key and direction
        usort($items, function ($a, $b) use ($query) {
            if ($query->getDirection() === Direction::ASC) {
                return $a[$this->sortKeyName] > $b[$this->sortKeyName];
            }

            return $a[$this->sortKeyName] < $b[$this->sortKeyName];
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
        $item[$this->partitionKeyName] = $partitionKeyValue;
        $item[$this->sortKeyName] = $sortKeyValue;

        $args = [
            'TableName' => $this->tableName,
            'Item' => $this->marshalerAdapter->marshalItem($item)
        ];

        $this->dynamoDbAdapter->putItem($args);
    }
}
