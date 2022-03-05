<?php

namespace Ajegu\DdbAccess;

use Ajegu\DdbAccess\Contract\QueryInterface;
use Ajegu\DdbAccess\Exception\DynamoDbErrorException;
use Ajegu\DdbAccess\Exception\MarshalerErrorException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class DynamoDbAdapter
{
    /**
     * @param DynamoDbClient $dynamoDbClient
     * @param Marshaler $marshaler
     * @param LoggerInterface $logger
     */
    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private Marshaler $marshaler,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param QueryInterface $query
     * @return array
     * @throws DynamoDbErrorException
     * @throws MarshalerErrorException
     */
    public function query(QueryInterface $query): array
    {
        try {
            $partitionKeyMarshalled =$this->marshaler->marshalValue($query->getPartitionKeyValue());
        } catch (UnexpectedValueException $exception) {
            $message = 'The partition key value can not be marshalled.';

            $this->logger->error($message, [
                'partitionKeyValue' => $query->getPartitionKeyValue(),
                'exception' => $exception->getMessage()
            ]);

            throw new MarshalerErrorException($message);
        }

        $args = [
            'TableName' => $query->getTableName(),
            'KeyConditionExpression' => $query->getPartitionKeyName() .' = :partitionKeyValue',
            'ExpressionAttributeValues' => [
                ':partitionKeyValue' => $partitionKeyMarshalled
            ]
        ];

        try {
            $result = $this->dynamoDbClient->query($args);
        } catch (DynamoDbException $exception) {
            $message = 'The query can not be executed.';

            $this->logger->error($message, [
                'args' => $args,
                'exception' => $exception
            ]);

            throw new DynamoDbErrorException($message);
        }

        return array_map(function(array $item) {
            try {
                return $this->marshaler->unmarshalItem($item);
            } catch (UnexpectedValueException $exception) {
                $message = 'The item can not be unmarshalled.';

                $this->logger->error($message, [
                    'item' => $item,
                    'exception' => $exception
                ]);

                throw new MarshalerErrorException($message);
            }
        }, $result->get('Items'));
    }
}