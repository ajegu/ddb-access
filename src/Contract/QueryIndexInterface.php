<?php

namespace Ajegu\DdbAccess\Contract;

interface QueryIndexInterface
{
    /**
     * @return string
     */
    public function getField(): string;

    /**
     * @return string
     */
    public function getValue(): string;
}