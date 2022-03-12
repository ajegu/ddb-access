<?php

namespace Ajegu\DdbAccess\Contract;

interface ItemBuilderInterface
{
    public function isSupported(string $partitionKey, string $sortKey): bool;
    public function beforeSave(array $item): array;
    public function afterQuery(array $item): array;
}