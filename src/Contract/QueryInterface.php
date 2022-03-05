<?php

namespace Ajegu\DdbAccess\Contract;

interface QueryInterface
{
    public function getTableName(): string;
    public function getPartitionKeyName(): string;
    public function getPartitionKeyValue(): string;
}