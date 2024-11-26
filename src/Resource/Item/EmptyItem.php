<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use ImagickPixel;
use stdClass;

class EmptyItem implements ItemInterface
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        $result = new Imagick();
        $result->newImage($width, $height, new ImagickPixel('transparent'));
        return $result;
    }
}
