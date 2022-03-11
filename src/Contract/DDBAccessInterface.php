<?php

namespace Ajegu\DdbAccess\Contract;

use Ajegu\DdbAccess\Exception\DynamoDbErrorException;
use Ajegu\DdbAccess\Exception\MarshalerErrorException;

interface DDBAccessInterface
{
    /**
     * @param QueryInterface $query
     * @return ResultInterface
     * @throws DynamoDbErrorException
     * @throws MarshalerErrorException
     */
    public function findAll(QueryInterface $query): ResultInterface;
}