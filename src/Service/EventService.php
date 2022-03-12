<?php

namespace Ajegu\DdbAccess\Service;

use Ajegu\DdbAccess\Contract\ItemBuilderInterface;
use Ajegu\DdbAccess\Model\Event;

class EventService
{
    /**
     * @param ItemBuilderInterface[] $builders
     */
    public function __construct(
        private iterable $builders = []
    ) {}

    /**
     * @param array $item
     * @param string $partitionKeyValue
     * @param string $sortKeyValue
     * @param Event $event
     * @return array
     */
    public function dispatch(array $item, string $partitionKeyValue, string $sortKeyValue, Event $event): array
    {
        foreach ($this->builders as $builder) {
            if ($builder->isSupported($partitionKeyValue, $sortKeyValue)) {
                return match ($event) {
                    Event::AFTER_QUERY => $builder->afterQuery($item),
                    Event::BEFORE_SAVE => $builder->beforeSave($item),
                };
            }
        }

        return $item;
    }
}