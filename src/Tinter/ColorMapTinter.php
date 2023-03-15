<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickException;
use ImagickPixel;

abstract class ColorMapTinter implements Tinterface
{
    /**
     * @param ResourceManagerInterface $resourceManager
     * @param int $index
     */
    public function __construct(protected ResourceManagerInterface $resourceManager, protected int $index = 0)
    {
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     * @throws TextureResolutionException
     */
    public function getTintColor(int $index): ?ImagickPixel
    {
        if ($index !== $this->index) {
            return null;
        }
        $map = $this->resourceManager->getTexture($this->getColorMap());
        $mapImage = $map->getImage();
        $width = $mapImage->getImageWidth();
        $height = $mapImage->getImageHeight();
        return $mapImage->getImagePixelColor(floor($width / 2), floor($height / 2));
    }

    /**
     * @return ResourceLocator
     */
    abstract protected function getColorMap(): ResourceLocator;
}
