<?php

namespace Ajegu\DdbAccess\Contract;

use Ajegu\DdbAccess\Model\Direction;

interface QueryInterface
{
    public function getPartitionKeyValue(): string;
    public function getCursor(): ?string;
    public function getDirection(): Direction;
    public function getPageSize(): ?int;
}