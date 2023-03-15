<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource;

use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\Texture\ImagickTexture;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureMeta;
use Exception;
use stdClass;

class UnstitchAtlasTextureSource extends AtlasTextureSource
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getTextureLocator(ResourceLocator $locator): ?ResourceLocator
    {
        $sprite = $this->getSpriteInfo($locator);
        if ($sprite === null) {
            return null;
        }
        return parent::getTextureLocator(ResourceLocator::parse($this->settings->resource, $this->namespace));
    }

    /**
     * @param ResourceLocator $locator
     * @return stdClass|null
     * @throws Exception
     */
    protected function getSpriteInfo(ResourceLocator $locator): ?stdClass
    {
        foreach ($this->settings->regions as $region) {
            if ($locator->is(ResourceLocator::parse($region->sprite, $this->namespace))) {
                return $region;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getTexture(ResourceLocator $locator): TextureInterface
    {
        $texture = parent::getTexture($locator);
        $spriteInfo = $this->getSpriteInfo($locator);
        $image = clone $texture->getImage();

        $divisorX = $this->settings->divisor_x;
        $divisorY = $this->settings->divisor_y;
        $baseWidth = $image->getImageWidth();
        $baseHeight = $image->getImageWidth();

        $image->cropImage(
            round($spriteInfo->width / $divisorX * $baseWidth),
            round($spriteInfo->height / $divisorY * $baseHeight),
            round($spriteInfo->x / $divisorX * $baseWidth),
            round($spriteInfo->y / $divisorY * $baseHeight)
        );

        return new ImagickTexture($image, new TextureMeta());
    }
}
