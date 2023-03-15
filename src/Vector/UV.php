<?php

namespace Aternos\Renderchest\Vector;

class UV extends Vector
{

    public function __construct(public float $u, public float $v)
    {
    }

    /**
     * @inheritDoc
     */
    function getValues(): array
    {
        return [$this->u, $this->v];
    }

    /**
     * @inheritDoc
     */
    function setValues(array $values): void
    {
        $this->u = $values[0];
        $this->v = $values[1];
    }
}
