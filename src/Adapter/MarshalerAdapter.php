<?php

namespace Ajegu\DdbAccess\Adapter;

use Ajegu\DdbAccess\Exception\MarshalerErrorException;
use Aws\DynamoDb\Marshaler;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class MarshalerAdapter
{
    /**
     * @param LoggerInterface $logger
     * @param Marshaler $marshaler
     */
    public function __construct(
        private LoggerInterface $logger,
        private Marshaler $marshaler
    ) {}

    /**
     * @param mixed $value
     * @return array
     * @throws MarshalerErrorException
     */
    public function marshalValue(mixed $value): array
    {
        try {
            return $this->marshaler->marshalValue($value);
        } catch (UnexpectedValueException $exception) {
            $message = 'The partition key value can not be marshalled.';

            $this->logger->error($message, [
                'value' => $value,
                'exception' => $exception->getMessage()
            ]);

            throw new MarshalerErrorException($message);
        }
    }

    /**
     * @param array $item
     * @return array
     * @throws MarshalerErrorException
     */
    public function unmarshalItem(array $item): array
    {
        try {
            return $this->marshaler->unmarshalItem($item);
        } catch (UnexpectedValueException $exception) {
            $message = 'The item can not be unmarshalled.';

            $this->logger->error($message, [
                'item' => $item,
                'exception' => $exception->getMessage()
            ]);

            throw new MarshalerErrorException($message);
        }
    }

    /**
     * @param array $item
     * @return array
     * @throws MarshalerErrorException
     */
    public function marshalItem(array $item): array
    {
        try {
            return $this->marshaler->marshalItem($item);
        } catch (UnexpectedValueException $exception) {
            $message = 'The item can not be marshalled.';

            $this->logger->error($message, [
                'item' => $item,
                'exception' => $exception->getMessage()
            ]);

            throw new MarshalerErrorException($message);
        }
    }
}