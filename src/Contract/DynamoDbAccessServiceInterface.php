<?php

namespace Ajegu\DdbAccess\Contract;

use Ajegu\DdbAccess\Exception\DynamoDbErrorException;
use Ajegu\DdbAccess\Exception\MarshalerErrorException;

interface DynamoDbAccessServiceInterface
{
    /**
     * @param string $partitionKeyValue
     * @return array
     * @throws DynamoDbErrorException
     * @throws MarshalerErrorException
     */
    public function findAll(string $partitionKeyValue): array;
}