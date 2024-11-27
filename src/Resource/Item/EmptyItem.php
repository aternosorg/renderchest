<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use ImagickPixel;
use stdClass;

class EmptyItem extends AbstractItem
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static
    {
        return new static($properties);
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
