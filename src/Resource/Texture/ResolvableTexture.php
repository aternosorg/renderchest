<?php

namespace Aternos\Renderchest\Resource\Texture;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Exception;
use Imagick;

class ResolvableTexture implements TextureInterface
{
    protected bool $isBeingResolved = false;

    public function __construct(protected TextureList $list, protected string $name)
    {
    }

    /**
     * @inheritDoc
     * @throws TextureResolutionException
     * @throws Exception
     */
    function getImage(int $animationTick = 0): Imagick
    {
        if ($this->isBeingResolved) {
            throw new Exception("Circular texture reference detected");
        }
        $this->isBeingResolved = true;
        $image = $this->list->get($this->name)->getImage($animationTick);
        $this->isBeingResolved = false;
        return $image;
    }

    /**
     * @inheritDoc
     * @throws TextureResolutionException
     * @throws Exception
     */
    public function getAnimationTickLength(): int
    {
        if ($this->isBeingResolved) {
            throw new Exception("Circular texture reference detected");
        }
        $this->isBeingResolved = true;
        $length = $this->list->get($this->name)->getAnimationTickLength();
        $this->isBeingResolved = false;
        return $length;
    }

    /**
     * @inheritDoc
     * @throws TextureResolutionException
     * @throws Exception
     */
    public function resolveAnimationFrame(int $animationTick): ResolvedAnimationFrame
    {
        if ($this->isBeingResolved) {
            throw new Exception("Circular texture reference detected");
        }
        $this->isBeingResolved = true;
        $frame = $this->list->get($this->name)->resolveAnimationFrame($animationTick);
        $this->isBeingResolved = false;
        return $frame;
    }

    /**
     * @inheritDoc
     * @throws TextureResolutionException
     * @throws Exception
     */
    public function getMeta(): TextureMeta
    {
        if ($this->isBeingResolved) {
            throw new Exception("Circular texture reference detected");
        }
        $this->isBeingResolved = true;
        $meta = $this->list->get($this->name)->getMeta();
        $this->isBeingResolved = false;
        return $meta;
    }
}
