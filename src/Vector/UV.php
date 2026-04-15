<?php

namespace Aternos\Renderchest\Vector;

class UV extends Vector
{
    /**
     * @param float[] $data
     * @return static
     */
    public static function fromData(array $data): static
    {
        return new static($data[0] / 16, $data[1] / 16);
    }

    /**
     * @param float $u
     * @param float $v
     */
    public function __construct(
        public float $u,
        public float $v
    )
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
