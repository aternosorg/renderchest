<?php

namespace Aternos\Renderchest\Tinter;

use ImagickPixel;
use ImagickPixelException;

class FishTinter implements Tinterface
{
    const COLORS = [
        "#f9fffe",
        "#f9801d",
        "#c74ebd",
        "#3ab3da",
        "#fed83d",
        "#80c71f",
        "#f38baa",
        "#474f52",
        "#9d9d97",
        "#169c9c",
        "#8932b8",
        "#3c44aa",
        "#835432",
        "#5e7c16",
        "#b02e26",
        "#1d1d21"
    ];

    protected array $cache = [];

    /**
     * @inheritDoc
     * @throws ImagickPixelException
     */
    public function getTintColor(int $index): ?ImagickPixel
    {
        if (!isset($this->cache[$index])) {
            $this->cache[$index] = static::COLORS[array_rand(static::COLORS)];
        }

        return new ImagickPixel($this->cache[$index]);
    }
}
