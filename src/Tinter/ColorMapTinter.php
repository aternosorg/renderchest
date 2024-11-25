<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickException;
use ImagickPixel;

abstract class ColorMapTinter implements Tinterface
{
    protected float $sampleX = 0.5;
    protected float $sampleY = 0.5;

    /**
     * @param ResourceManagerInterface $resourceManager
     */
    public function __construct(protected ResourceManagerInterface $resourceManager)
    {
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     * @throws TextureResolutionException
     */
    public function getTintColor(): ?ImagickPixel
    {
        $map = $this->resourceManager->getTexture($this->getColorMap());
        $mapImage = $map->getImage();
        $width = $mapImage->getImageWidth();
        $height = $mapImage->getImageHeight();
        return $mapImage->getImagePixelColor(floor($width * $this->sampleX), floor($height * $this->sampleY));
    }

    /**
     * @param float $x
     * @param float $y
     * @return $this
     */
    public function setSamplePosition(float $x, float $y): static
    {
        $this->sampleX = $x;
        $this->sampleY = $y;
        return $this;
    }

    /**
     * @return ResourceLocator
     */
    abstract protected function getColorMap(): ResourceLocator;
}
