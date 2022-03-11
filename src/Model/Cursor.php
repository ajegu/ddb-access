<?php

namespace Ajegu\DdbAccess\Model;

use Ajegu\DdbAccess\Contract\CursorInterface;

class Cursor implements CursorInterface
{
    private ?string $previous = null;
    private ?string $next = null;

    /**
     * @return string|null
     */
    public function getPrevious(): ?string
    {
        return $this->previous;
    }

    /**
     * @param string|null $previous
     */
    public function setPrevious(?string $previous): void
    {
        $this->previous = $previous;
    }

    /**
     * @return string|null
     */
    public function getNext(): ?string
    {
        return $this->next;
    }

    /**
     * @param string|null $next
     */
    public function setNext(?string $next): void
    {
        $this->next = $next;
    }


}