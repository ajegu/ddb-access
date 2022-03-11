<?php

namespace Ajegu\DdbAccess\Contract;

interface ResultInterface
{
    public function getData(): array;
    public function getCursor(): CursorInterface;
}