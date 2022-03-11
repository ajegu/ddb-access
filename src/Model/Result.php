<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\CursorInterface;
use Ajegu\DdbAccess\Contract\ResultInterface;

class Result implements ResultInterface
{
    /**
     * @param array $data
     * @param CursorInterface $cursor
     */
    public function __construct(
        private array $data,
        private CursorInterface $cursor
    ) {}

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return CursorInterface
     */
    public function getCursor(): CursorInterface
    {
        return $this->cursor;
    }


}