<?php

namespace Ajegu\DdbAccess\Contract;

interface CursorInterface
{
    public function getPrevious(): ?string;
    public function getNext(): ?string;
}