<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource;

use Aternos\Renderchest\Resource\AtlasSource\TextureSource\Permutations\PermutatedTextureInfo;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\ImagickTexture;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureMeta;
use Exception;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIteratorException;
use stdClass;

class PalettedPermutationsTextureSource extends AtlasTextureSource
{
    /**
     * @var ResourceLocator[]
     */
    protected array $baseTextures = [];

    /**
     * @var PermutatedTextureInfo[]
     */
    protected array $textures = [];

    protected ?array $keyPalette = null;

    protected string $separator = "_";

    /**
     * @throws Exception
     */
    public function __construct(ResourceManagerInterface $resourceManager, string $namespace, stdClass $settings)
    {
        parent::__construct($resourceManager, $namespace, $settings);

        if (isset($settings->separator) && is_string($settings->separator)) {
            $this->separator = $settings->separator;
        }

        foreach ($settings->textures as $texture) {
            $this->baseTextures[] = ResourceLocator::parse($texture);
        }

        foreach ($settings->permutations as $name => $permutation) {
            $locator = ResourceLocator::parse($permutation);

            foreach ($this->baseTextures as $baseTexture) {
                $this->textures[] = new PermutatedTextureInfo($baseTexture, $locator, $name, $this->separator);
            }
        }
    }

    /**
     * @param ResourceLocator $locator
     * @return PermutatedTextureInfo|null
     * @throws Exception
     */
    protected function getTextureInfo(ResourceLocator $locator): ?PermutatedTextureInfo
    {
        foreach ($this->textures as $texture) {
            if ($texture->getLocator()->is($locator)) {
                return $texture;
            }
        }
        return null;
    }

    /**
     * @param ResourceLocator $locator
     * @return bool
     * @throws Exception
     */
    public function has(ResourceLocator $locator): bool
    {
        return !!$this->getTextureInfo($locator);
    }

    /**
     * @param ImagickPixel $color
     * @return int
     * @throws ImagickPixelException
     */
    protected function encodeColor(ImagickPixel $color): int
    {
        return (round($color->getColorValue(Imagick::COLOR_RED) * 255) << 24) |
            ((round($color->getColorValue(Imagick::COLOR_GREEN) * 255)) << 16) |
            (round($color->getColorValue(Imagick::COLOR_BLUE) * 255) << 8) |
            round($color->getColorValue(Imagick::COLOR_ALPHA) * 255);
    }

    /**
     * @return int[]
     * @throws ImagickException
     * @throws ImagickPixelIteratorException
     * @throws ImagickPixelException
     * @throws Exception
     */
    protected function getKeyPalette(): array
    {
        if ($this->keyPalette === null) {
            $texture = parent::getTexture(ResourceLocator::parse($this->settings->palette_key))->getImage();
            $width = $texture->getImageWidth();
            $imageIterator = $texture->getPixelIterator();
            $this->keyPalette = [];

            foreach ($imageIterator as $row => $pixels) {
                foreach ($pixels as $column => $pixel) {
                    /** @var $pixel ImagickPixel */
                    $this->keyPalette[$this->encodeColor($pixel)] = $row * $width + $column;
                }
                $imageIterator->syncIterator();
            }

        }
        return $this->keyPalette;
    }

    /**
     * @inheritDoc
     */
    public function getTexture(ResourceLocator $locator): TextureInterface
    {
        $info = $this->getTextureInfo($locator);
        $texture = parent::getTexture($info->getBaseTextureLocator());
        $image = clone $texture->getImage();

        $permutation = parent::getTexture($info->getPermutationLocator())->getImage();
        $width = $permutation->getImageWidth();

        $imageIterator = $image->getPixelIterator();

        foreach ($imageIterator as $pixels) {
            foreach ($pixels as $pixel) {
                /** @var $pixel ImagickPixel */
                $encoded = $this->encodeColor($pixel);
                $replacementPos = $this->getKeyPalette()[$encoded] ?? null;
                if ($replacementPos === null) {
                    continue;
                }

                $x = $replacementPos % $width;
                $y = intdiv($replacementPos, $width);
                $pixel->setColorFromPixel($permutation->getImagePixelColor($x, $y));
            }
            $imageIterator->syncIterator();
        }

        return new ImagickTexture($image, new TextureMeta());
    }
}
