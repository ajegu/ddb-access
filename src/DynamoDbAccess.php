<?php

namespace Ajegu\DdbAccess;

use Ajegu\DdbAccess\Contract\DynamoDbAccessServiceInterface;
use Ajegu\DdbAccess\Exception\DynamoDbErrorException;
use Ajegu\DdbAccess\Exception\MarshalerErrorException;
use Ajegu\DdbAccess\Model\Query;

class DynamoDbAccess implements DynamoDbAccessServiceInterface
{
    /**
     * @param DynamoDbAdapter $dynamoDbAdapter
     * @param string $tableName DynamoDb table name
     * @param string $partitionKeyName DynamoDb partition key name
     */
    public function __construct(
        private DynamoDbAdapter $dynamoDbAdapter,
        private string $tableName,
        private string $partitionKeyName
    ) {}

    /**
     * @param string $partitionKeyValue
     * @return array
     * @throws DynamoDbErrorException
     * @throws MarshalerErrorException
     */
    public function findAll(string $partitionKeyValue): array
    {
        $query = new Query(
            $this->tableName,
            $this->partitionKeyName,
            $partitionKeyValue
        );

        return $this->dynamoDbAdapter->query($query);
    }
}