<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\QueryInterface;

class Query implements QueryInterface
{
    private string $partitionKeyValue; // DynamoDb partition key value
    private ?string $cursor; // Cursor encoded value
    private Direction $direction; // ASC|DESC (default: ASC)
    private ?int $pageSize;


    public function __construct() {
        $this->direction = Direction::ASC;
    }

    /**
     * @return string
     */
    public function getPartitionKeyValue(): string
    {
        return $this->partitionKeyValue;
    }

    /**
     * @param string $partitionKeyValue
     */
    public function setPartitionKeyValue(string $partitionKeyValue): void
    {
        $this->partitionKeyValue = $partitionKeyValue;
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


}