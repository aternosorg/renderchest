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
        $x = floor($this->sampleX * $width);
        $y = floor($this->sampleY * $height);

        $index = $y * $width + $x;
        if ($index < 0 || $index >= $width * $height) {
            return new ImagickPixel("#ff00ff");
        }

        $x = floor($index % $width);
        $y = intdiv($index, $width);

        return $mapImage->getImagePixelColor($x, $y);
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
