<?php

namespace Aternos\Renderchest\Resource\Item\Parts;

use Aternos\Renderchest\Resource\Item\ItemInterface;

class SelectCase
{
    /**
     * @param array $values
     * @param ItemInterface $item
     */
    public function __construct(
        protected array         $values,
        protected ItemInterface $item
    )
    {
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function match(mixed $value): bool
    {
        return in_array($value, $this->values);
    }
}
