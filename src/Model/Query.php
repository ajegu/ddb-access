<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\QueryIndexInterface;
use Ajegu\DdbAccess\Contract\QueryInterface;

class Query implements QueryInterface
{
    private string $partitionKey; // DynamoDb partition key value
    private ?string $sortKey; // DynamoDb sort key value
    private ?string $cursor; // Cursor encoded value
    private Direction $direction; // ASC|DESC (default: ASC)
    private ?int $pageSize;
    private ?QueryIndexInterface $index;


    public function __construct() {
        $this->direction = Direction::ASC;
    }

    /**
     * @return string
     */
    public function getPartitionKey(): string
    {
        return $this->partitionKey;
    }

    /**
     * @param string $partitionKey
     */
    public function setPartitionKey(string $partitionKey): void
    {
        $this->partitionKey = $partitionKey;
    }

    /**
     * @return string|null
     */
    public function getSortKey(): ?string
    {
        return $this->sortKey;
    }

    /**
     * @param string|null $sortKey
     */
    public function setSortKey(?string $sortKey): void
    {
        $this->sortKey = $sortKey;
    }

    /**
     * @return string|null
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * @param string|null $cursor
     */
    public function setCursor(?string $cursor): void
    {
        $this->cursor = $cursor;
    }

    /**
     * @return Direction
     */
    public function getDirection(): Direction
    {
        return $this->direction;
    }

    /**
     * @param Direction $direction
     */
    public function setDirection(Direction $direction): void
    {
        $this->direction = $direction;
    }

    /**
     * @return int|null
     */
    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    /**
     * @param int|null $pageSize
     */
    public function setPageSize(?int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @return QueryIndexInterface|null
     */
    public function getIndex(): ?QueryIndexInterface
    {
        return $this->index;
    }

    /**
     * @param QueryIndexInterface|null $index
     */
    public function setIndex(?QueryIndexInterface $index): void
    {
        $this->index = $index;
    }

}