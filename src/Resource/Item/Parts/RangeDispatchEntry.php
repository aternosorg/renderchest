<?php

namespace Aternos\Renderchest\Resource\Item\Parts;

use Aternos\Renderchest\Resource\Item\ItemInterface;

class RangeDispatchEntry
{
    /**
     * @param float $threshold
     * @param ItemInterface $item
     */
    public function __construct(
        protected float $threshold,
        protected ItemInterface $item
    )
    {
    }

    /**
     * @return float
     */
    public function getThreshold(): float
    {
        return $this->threshold;
    }

    /**
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * @param float $value
     * @return bool
     */
    public function match(float $value): bool
    {
        return $value >= $this->threshold;
    }
}
