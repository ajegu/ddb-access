<?php

namespace Ajegu\DdbAccess\Contract;

interface TableDefinitionInterface
{
    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @return string
     */
    public function getPartitionKey(): string;

    /**
     * @return string|null
     */
    public function getSortKey(): ?string;

    /**
     * @return IndexInterface[]|null
     */
    public function getIndexes(): ?array;
}