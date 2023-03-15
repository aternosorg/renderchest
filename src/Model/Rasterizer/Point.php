<?php

namespace Aternos\Renderchest\Model\Rasterizer;

use Aternos\Renderchest\Vector\UV;
use Aternos\Renderchest\Vector\Vector2;

class Point
{
    public function __construct(protected Vector2 $vector, protected UV $uv, protected float $depth = 0)
    {
    }

    /**
     * @return Vector2
     */
    public function getVector(): Vector2
    {
        return $this->vector;
    }

    /**
     * @return UV
     */
    public function getUv(): UV
    {
        return $this->uv;
    }

    /**
     * @return float
     */
    public function getDepth(): float
    {
        return $this->depth;
    }
}
