<?php

namespace Ajegu\DdbAccess\Contract;

interface IndexInterface
{
    public function getName(): string;
    public function getField(): string;
}