<?php

namespace Ajegu\DdbAccess\Adapter;

use Ajegu\DdbAccess\Exception\DynamoDbErrorException;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\Result;
use Psr\Log\LoggerInterface;

class DynamoDbAdapter
{
    /**
     * @param LoggerInterface $logger
     * @param DynamoDbClient $dynamoDbClient
     */
    public function __construct(
        private LoggerInterface $logger,
        private DynamoDbClient $dynamoDbClient
    ) {}

    /**
     * @param array $args
     * @return Result
     * @throws DynamoDbErrorException
     */
    public function query(array $args): Result
    {
        try {
            return $this->dynamoDbClient->query($args);
        } catch (DynamoDbException $exception) {
            $message = 'The query can not be executed.';

            $this->logger->error($message, [
                'args' => $args,
                'exception' => $exception->getMessage()
            ]);

            throw new DynamoDbErrorException($message);
        }
    }
}