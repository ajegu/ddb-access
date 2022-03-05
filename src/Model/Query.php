<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\QueryInterface;

class Query implements QueryInterface
{
    /**
     * @param string $tableName DynamoDb table name
     */
    public function __construct(
        private string $tableName,
        private string $partitionKeyName,
        private string $partitionKeyValue,
    ) {}

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getPartitionKeyName(): string
    {
        return $this->partitionKeyName;
    }

    /**
     * @return string
     */
    public function getPartitionKeyValue(): string
    {
        return $this->partitionKeyValue;
    }



}