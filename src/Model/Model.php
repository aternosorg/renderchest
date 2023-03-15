<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\Face\Face;
use Aternos\Renderchest\Model\Face\FaceImage;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\TinterCollection;
use Aternos\Renderchest\Tinter\Tinterface;
use Aternos\Renderchest\Util\ColorBlender;
use Aternos\Renderchest\Util\Math;
use Aternos\Renderchest\Util\PixelIterator;
use Aternos\Renderchest\Vector\Vector3;
use Exception;
use Imagick;
use ImagickDrawException;
use ImagickException;
use ImagickPixelException;
use ImagickPixelIteratorException;
use stdClass;

class Model implements ModelInterface
{
    protected float $combineTime = 0;
    protected float $renderTime = 0;
    protected ModelDisplaySettings $displaySettings;
    protected TextureList $textures;
    protected ModelGuiLight $guiLight = ModelGuiLight::FRONT;
    protected TinterCollection $tinters;

    /**
     * @var Element[]
     */
    protected array $elements = [];

    public function __construct()
    {
        $this->displaySettings = ModelDisplaySettings::getDefault();
        $this->textures = new TextureList();
        $this->tinters = new TinterCollection();
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
     * @return Element[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param int $width
     * @param int $height
     * @return Imagick
     * @throws ImagickDrawException
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException|TextureResolutionException
     */
    public function render(int $width, int $height): Imagick
    {
        $startT = microtime(true);

        /** @var Face[] $faces */
        $faces = [];
        $animationLengths = [];
        $interpolated = false;
        foreach ($this->elements as $i => $element) {
            foreach ($element->getFaces() as $face) {
                $faces[] = $face;
                $texture = $face->getFaceInfo()->getTexture();
                $animationLengths[] = $texture->getAnimationTickLength();
                $interpolated = $interpolated || $texture->getMeta()->isInterpolated();
            }
        }

        usort($faces, fn($a, $b) => -($b->getMaxZ() <=> $a->getMaxZ()));

        $animationTicks = max(1, Math::lcm($animationLengths));
        $maxSkipableTicks = $interpolated ? ceil($animationTicks / Constants::ANIMATION_MAX_FRAMES) : null;

        $lastAnimationFrames = null;
        $faceImages = [];
        $ticksSinceLastRender = 0;
        $result = new Imagick();
        for ($tick = 0; $tick < $animationTicks; $tick++) {
            $ticksSinceLastRender++;
            $frames = $this->resolveAnimationFrames($faces, $tick);
            if($lastAnimationFrames !== null && $frames === $lastAnimationFrames && $tick !== $animationTicks - 1 &&
                ($maxSkipableTicks === null || $ticksSinceLastRender < $maxSkipableTicks)) {
                continue;
            }

            foreach ($faces as $i => $face) {
                if($lastAnimationFrames !== null && $lastAnimationFrames[$i] === $frames[$i]) {
                    continue;
                }
                $res = $face->getPerspectiveImage($width, $height, $tick);
                if($res === null) {
                    continue;
                }

                $faceImages[$i] = $res;
            }

            $colorMap = $this->combineFaceImages($width, $height, $faceImages);

            if($tick !== 0) {
                $result->setImageDelay(Constants::ANIMATION_TICK_LENGTH * $ticksSinceLastRender);
            }

            $result->addImage($colorMap);
            $result->setImageDispose(2);
            $ticksSinceLastRender = 0;
            $lastAnimationFrames = $frames;
        }

        $result->setImageDelay(Constants::ANIMATION_TICK_LENGTH);
        $this->renderTime = microtime(true) - $startT;
        return $result;
    }

    /**
     * @param Face[] $faces
     * @param int $tick
     * @return int[]
     */
    protected function resolveAnimationFrames(array $faces, int $tick): array
    {
        $result = [];
        foreach ($faces as $face) {
            $result[] = $face->getFaceInfo()->getTexture()->resolveAnimationFrame($tick)->getFrame();
        }
        return $result;
    }

    /**
     * @param int $width
     * @param int $height
     * @param FaceImage[] $faceImages
     * @return Imagick
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    protected function combineFaceImages(int $width, int $height, array $faceImages): Imagick
    {
        $startT = microtime(true);

        foreach ($faceImages as $faceImage) {
            $faceImage->rewind();
        }

        $colorMap = new Imagick();
        $colorMap->newImage($width, $height, 'transparent');

        $result = (new PixelIterator($colorMap))->setSyncImage(true);
        while ($result->valid()) {
            usort($faceImages, function (FaceImage $a, FaceImage $b) use ($result) {
                return $a->currentDepth() <=> $b->currentDepth();
            });

            $pixel = $result->current();
            foreach ($faceImages as $faceImage) {
                $source = $faceImage->current();
                if($source !== null && $source->getColorValue(Imagick::COLOR_ALPHA) > 0.0) {
                    ColorBlender::blendColors($pixel, $source);
                }
                $faceImage->next();
            }

            $result->next();
        }

        $this->combineTime += microtime(true) - $startT;
        return $colorMap;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function applyModelData(stdClass $data, ResourceManagerInterface $resourceManager, ?Tinterface $tinter): void
    {
        if ($tinter !== null) {
            $this->tinters->addTinter($tinter);
        }

        $guiDisplay = $data->display?->gui ?? null;
        if ($guiDisplay) {
            $this->displaySettings = new ModelDisplaySettings(
                new Vector3(...($guiDisplay->rotation ?? [0, 0, 0])),
                new Vector3(...($guiDisplay->translation ?? [0, 0, 0])),
                new Vector3(...($guiDisplay->scale ?? [1, 1, 1]))
            );
        }

        foreach ($data->textures ?? [] as $name => $locator) {
            $this->textures->set($name, $locator, $resourceManager);
        }

        $light = $data->gui_light ?? null;
        if ($light) {
            $this->guiLight = ModelGuiLight::from($light);
        }

        if (isset($data->elements)) {
            $this->elements = [];
            foreach ($data->elements ?? [] as $element) {
                $this->elements[] = Element::fromModelData($element, $this->guiLight, $this->textures, $this->displaySettings, $this->tinters);
            }
        }
    }

    /**
     * @return float|int
     */
    public function getCombineTime(): float|int
    {
        return $this->combineTime;
    }

    /**
     * @return float|int
     */
    public function getRenderTime(): float|int
    {
        return $this->renderTime;
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
