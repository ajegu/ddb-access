<?php

namespace Ajegu\DdbAccess\Model;

class CursorValue
{
    private array $keys;
    private Direction $direction;

    /**
     * @param array $keys
     * @param Direction $direction
     */
    public function __construct(array $keys, Direction $direction)
    {
        $this->keys = $keys;
        $this->direction = $direction;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return Direction
     */
    public function getDirection(): Direction
    {
        return $this->direction;
    }


}