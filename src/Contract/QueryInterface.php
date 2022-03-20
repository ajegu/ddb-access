<?php

namespace Ajegu\DdbAccess\Contract;

use Ajegu\DdbAccess\Model\Direction;

interface QueryInterface
{
    /**
     * @return string
     */
    public function getPartitionKey(): string;

    /**
     * @return string|null
     */
    public function getSortKey(): ?string;

    /**
     * @return string|null
     */
    public function getCursor(): ?string;

    /**
     * @return Direction
     */
    public function getDirection(): Direction;

    /**
     * @return int|null
     */
    public function getPageSize(): ?int;

    /**
     * @return QueryIndexInterface|null
     */
    public function getIndex(): ?QueryIndexInterface;
}