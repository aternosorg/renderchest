<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\TinterList;
use Aternos\Renderchest\Util\ColorBlender;
use Aternos\Renderchest\Util\Math;
use Exception;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIteratorException;
use stdClass;

class GeneratedItem implements ModelInterface
{
    protected ModelDisplaySettings $displaySettings;
    protected TextureList $textures;
    protected ModelGuiLight $guiLight = ModelGuiLight::FRONT;

    public function __construct()
    {
        $this->displaySettings = ModelDisplaySettings::getDefault();
        $this->textures = new TextureList();
    }

    /**
     * @inheritDoc
     */
    public function getDisplaySettings(): ModelDisplaySettings
    {
        return $this->displaySettings;
    }

    /**
     * @inheritDoc
     */
    public function getTextures(): TextureList
    {
        return $this->textures;
    }

    /**
     * @inheritDoc
     */
    public function getGuiLight(): ModelGuiLight
    {
        return $this->guiLight;
    }

    /**
     * @inheritDoc
     * @return Imagick
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public function render(int $width, int $height, ?TinterList $tinters = null): Imagick
    {
        $layers = [];
        $animationLengths = [];
        $interpolated = false;
        foreach ($this->textures->getAll() as $name => $texture) {
            if (preg_match("#^layer(\d+)$#", $name, $matches)) {
                $layers[intval($matches[1])] = $texture;
                $animationLengths[] = $texture->getAnimationTickLength();
                $interpolated = $interpolated || $texture->getMeta()->isInterpolated();
            }
        }
        ksort($layers, SORT_NUMERIC);

        $animationTicks = max(1, Math::lcm($animationLengths));
        $maxSkipableTicks = $interpolated ? ceil($animationTicks / Constants::ANIMATION_MAX_FRAMES) : null;

        $lastAnimationFrames = null;
        $ticksSinceLastRender = 0;
        $result = new Imagick();
        for ($tick = 0; $tick < $animationTicks; $tick++) {
            $ticksSinceLastRender++;
            $frames = $this->resolveAnimationFrames($layers, $tick);
            if($lastAnimationFrames !== null && $frames === $lastAnimationFrames && $tick !== $animationTicks - 1 &&
                ($maxSkipableTicks === null || $ticksSinceLastRender < $maxSkipableTicks)) {
                continue;
            }
            $lastAnimationFrames = $frames;

            $texture = new Imagick();
            $texture->newImage($width, $height, new ImagickPixel('transparent'));

            foreach ($layers as $i => $layer) {
                $image = clone $layer->getImage($tick);

                if ($tinters !== null) {
                    $color = $tinters->getTintColor($i);
                    if ($color !== null) {
                        ColorBlender::tintImage($image, $color);
                    }
                }

                $image->resizeImage($width, $height, Imagick::FILTER_POINT, 0);
                $texture->compositeImage($image, Imagick::COMPOSITE_DEFAULT, 0, 0);
            }

            if($tick !== 0) {
                $result->setImageDelay(Constants::ANIMATION_TICK_LENGTH * $ticksSinceLastRender);
            }

            $result->addImage($texture);
            $result->setImageDispose(2);
            $ticksSinceLastRender = 0;
        }

        $result->setImageDelay(Constants::ANIMATION_TICK_LENGTH);
        return $result;
    }

    /**
     * @param TextureInterface[] $layers
     * @param int $tick
     * @return int[]
     */
    protected function resolveAnimationFrames(array $layers, int $tick): array
    {
        $result = [];
        foreach ($layers as $layer) {
            $result[] = $layer->resolveAnimationFrame($tick)->getFrame();
        }
        return $result;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function applyModelData(stdClass $data, ResourceManagerInterface $resourceManager): void
    {
        foreach ($data->textures ?? [] as $name => $locator) {
            $this->textures->set($name, $locator, $resourceManager);
        }
    }

    /**
     * @inheritDoc
     * @throws TextureResolutionException
     */
    public function hasAnimatedTextures(): bool
    {
        foreach ($this->textures->getAll() as $texture) {
            if ($texture->getAnimationTickLength() > 1) {
                return true;
            }
        }
        return false;
    }
}
