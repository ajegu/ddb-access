<?php

namespace Ajegu\DdbAccess\Service;

use Ajegu\DdbAccess\Contract\QueryInterface;
use Ajegu\DdbAccess\Exception\CursorException;
use Ajegu\DdbAccess\Model\Cursor;
use Ajegu\DdbAccess\Model\CursorValue;
use Ajegu\DdbAccess\Model\Direction;
use JsonException;
use Psr\Log\LoggerInterface;

class CursorService
{
    /**
     * @param LoggerInterface $logger
     * @param string $partitionKeyName
     * @param string|null $sortKeyName
     */
    public function __construct(
        private LoggerInterface $logger,
        private string          $partitionKeyName,
        private ?string         $sortKeyName = null
    )
    {
    }

    /**
     * @param array $items
     * @param QueryInterface $query
     * @param array|null $lastEvaluatedKey
     * @return Cursor
     * @throws CursorException
     */
    public function build(array $items, QueryInterface $query, ?array $lastEvaluatedKey): Cursor
    {
        $cursor = new Cursor();

        $queryCursorValue = null;
        if ($queryCursor = $query->getCursor()) {
            $queryCursorValue = $this->decode($queryCursor);
        }

        if (count($items) === 0) {
            // Case if no items (previous request has a LastEvaluatedKey but no more result)
            if ($queryCursorValue) {
                if ($queryCursorValue->getDirection() === Direction::ASC) {
                    $this->addPrevious($queryCursorValue->getKeys(), $cursor);
                } else {
                    $this->addNext($queryCursorValue->getKeys(), $cursor);
                }
            }
            return $cursor;
        }

        if ($queryCursorValue) {
            // Cases page2 to nextPage, or page3 to previousPage
            if ($queryCursorValue->getDirection() === $query->getDirection()) {
                // Case page2 to nextPage if the nextPage exists
                if ($lastEvaluatedKey) {
                    $this->addNext(end($items), $cursor);
                }
                // prev always exists
                $this->addPrevious($items[0], $cursor);

            } else {
                // Case page3 to previousPage if previous page exists
                if ($lastEvaluatedKey) {
                    $this->addPrevious(end($items), $cursor);
                }
                $this->addNext($items[0], $cursor);
            }

        } else if ($lastEvaluatedKey) {
            // Cases firstPage to nextPage or lastPage to previousPage
            if ($query->getDirection() === Direction::ASC) {
                // Case firstPage to nextPage
                $this->addNext(end($items), $cursor);
            } else {
                // Case lastPage to previousPage
                $this->addPrevious($items[0], $cursor);
            }
        }

        return $cursor;
    }

    /**
     * @param string $cursor
     * @return CursorValue
     * @throws CursorException
     */
    public function decode(string $cursor): CursorValue
    {
        try {
            $data = json_decode(
                base64_decode($cursor),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            $message = 'The cursor can not be decoded.';

            $this->logger->error($message, [
                'cursor' => $cursor,
                'exception' => $exception->getMessage()
            ]);

            throw new CursorException($message);
        }

        $direction = $data['direction'] === Direction::ASC->value ? Direction::ASC : Direction::DESC;
        return new CursorValue($data['keys'], $direction);
    }

    /**
     * @param CursorValue $cursorValue
     * @return string
     * @throws CursorException
     */
    public function encode(CursorValue $cursorValue): string
    {
        $value = [
            'keys' => $cursorValue->getKeys(),
            'direction' => $cursorValue->getDirection()
        ];

        try {
            return base64_encode(json_encode($value, JSON_THROW_ON_ERROR));
        } catch (JsonException $exception) {
            $message = 'The cursor can not be encoded.';

            $this->logger->error($message, [
                'value' => $value,
                'exception' => $exception->getMessage()
            ]);

            throw new CursorException($message);
        }
    }

    /**
     * @param array $item
     * @return array
     */
    private function buildKeys(array $item): array
    {
        return [
            $this->partitionKeyName => $item[$this->partitionKeyName],
            $this->sortKeyName => $item[$this->sortKeyName],
        ];
    }

    /**
     * @param array $item
     * @param Cursor $cursor
     * @return void
     * @throws CursorException
     */
    private function addNext(array $item, Cursor $cursor): void
    {
        $next = new CursorValue($this->buildKeys($item), Direction::ASC);
        $cursor->setNext($this->encode($next));
    }

    /**
     * @param array $item
     * @param Cursor $cursor
     * @return void
     * @throws CursorException
     */
    private function addPrevious(array $item, Cursor $cursor): void
    {
        $prev = new CursorValue($this->buildKeys($item), Direction::DESC);
        $cursor->setPrevious($this->encode($prev));
    }
}
