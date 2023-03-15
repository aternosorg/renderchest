<?php

namespace Aternos\Renderchest\Vector;

class Vector2 extends Vector
{
    protected int $i = 0;

    public function __construct(public float $x, public float $y)
    {
    }

    /**
     * @return array
     */
    function getValues(): array
    {
        return [$this->x, $this->y];
    }

    /**
     * @inheritDoc
     */
    function setValues(array $values): void
    {
        [$this->x, $this->y] = $values;
    }
}
