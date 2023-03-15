<?php

namespace Aternos\Renderchest\Resource\Texture;

class ResolvedAnimationFrame
{
    public function __construct(protected int $frame, protected float $interpolation)
    {
    }

    /**
     * @return int
     */
    public function getFrame(): int
    {
        return $this->frame;
    }

    /**
     * @return float
     */
    public function getInterpolation(): float
    {
        return $this->interpolation;
    }
}

