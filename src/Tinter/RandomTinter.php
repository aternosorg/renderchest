<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickPixel;
use ImagickPixelException;
use stdClass;

class RandomTinter implements Tinterface
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

    protected ?ImagickPixel $cache = null;

    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelException
     */
    public function getTintColor(): ?ImagickPixel
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        return $this->cache = new ImagickPixel(static::COLORS[array_rand(static::COLORS)]);
    }
}
