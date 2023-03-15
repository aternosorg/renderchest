<?php

namespace Aternos\Renderchest\Tinter;

use ImagickPixel;
use ImagickPixelException;

class FixedColorTinter implements Tinterface
{
    /**
     * @var string[]
     */
    protected array $colors;

    public function __construct(string ...$color)
    {
        $this->colors = $color;
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelException
     */
    public function getTintColor(int $index): ?ImagickPixel
    {
        if (!isset($this->colors[$index])) {
            return null;
        }
        return new ImagickPixel($this->colors[$index]);
    }
}
