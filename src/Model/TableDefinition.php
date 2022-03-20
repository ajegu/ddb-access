<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\TableDefinitionInterface;

class TableDefinition implements TableDefinitionInterface
{
    /**
     * @param string $table
     * @param string $partitionKey
     * @param string|null $sortKey
     * @param array|null $indexes
     */
    public function __construct(
        private string $table,
        private string $partitionKey,
        private ?string $sortKey,
        private ?array $indexes
    ) {}

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getPartitionKey(): string
    {
        return $this->partitionKey;
    }

    /**
     * @return string|null
     */
    public function getSortKey(): ?string
    {
        return $this->sortKey;
    }

    /**
     * @return array|null
     */
    public function getIndexes(): ?array
    {
        return $this->indexes;
    }


}