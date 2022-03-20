<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\QueryIndexInterface;

class QueryIndex implements QueryIndexInterface
{
    /**
     * @param string $field
     * @param string $value
     */
    public function __construct(
        private string $field,
        private string $value
    ) {}

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


}