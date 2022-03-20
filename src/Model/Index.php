<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\IndexInterface;

class Index implements IndexInterface
{
    /**
     * @param string $name
     * @param string $field
     */
    public function __construct(
        private string $name,
        private string $field
    ) {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}