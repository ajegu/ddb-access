<?php

namespace Ajegu\DdbAccess\Exception;

use Ajegu\DdbAccess\Contract\DynamoDbAccessExceptionInterface;
use Exception;

abstract class AbstractException extends Exception implements DynamoDbAccessExceptionInterface
{

}