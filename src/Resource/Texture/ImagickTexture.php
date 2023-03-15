<?php

namespace Aternos\Renderchest\Resource\Texture;

use Aternos\Renderchest\Util\ColorBlender;
use Imagick;
use ImagickException;
use ImagickPixelException;
use ImagickPixelIteratorException;

class ImagickTexture implements TextureInterface
{
    /**
     * @var Imagick[]
     */
    protected array $frameImages = [];
    protected ?int $animationFrameCount = null;
    protected ?int $animationTickLength = null;

    /**
     * @param Imagick $image
     * @param TextureMeta $meta
     */
    public function __construct(protected Imagick $image, protected TextureMeta $meta)
    {
    }

    /**
     * @param int $frame
     * @return Imagick
     * @throws ImagickException
     */
    protected function cropImageFrame(int $frame): Imagick
    {
        $width = $this->image->getImageWidth();

        $cols = intval(16 / $this->meta->getWidth());

        $frameWidth = $width * ($this->meta->getWidth() / 16);
        $frameHeight = $frameWidth * ($this->meta->getHeight() / 16);

        $frames = $this->meta->getFrames();
        $frame = count($frames) ? $this->meta->getFrames()[$frame]?->getIndex() : $frame;
        $x = $frame % $cols * $frameWidth;
        $y = floor($frame / $cols) * $frameHeight;

        $image = clone $this->image;
        $image->cropImage($frameWidth, $frameHeight, $x, $y);
        $image->setImagePage(0, 0, 0, 0);
        return $image;
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     */
    public function resolveAnimationFrame(int $animationTick): ResolvedAnimationFrame
    {
        $frames = $this->meta->getFrames();
        if (count($frames) === 0) {
            $frame = floor($animationTick / $this->meta->getFrameTime()) % $this->getAnimationFrameCount();
            $interpolation = ($animationTick % $this->meta->getFrameTime()) / $this->meta->getFrameTime();
            return new ResolvedAnimationFrame($frame, $interpolation);
        }


        $currentFrameTime = 0;
        foreach ($frames as $i => $animationFrame) {
            $time = $animationFrame->getTime();
            if ($currentFrameTime + $time > $animationTick) {
                $interpolation = 1 - ($currentFrameTime + $time - $animationTick) / $time;
                return new ResolvedAnimationFrame($i, $interpolation);
            }
            $currentFrameTime += $time;
        }

        return $this->resolveAnimationFrame($animationTick - $currentFrameTime);
    }

    /**
     * Get the number of frames in the texture.
     *
     * @return int
     * @throws ImagickException
     */
    protected function getAnimationFrameCount(): int
    {
        if (!$this->meta->isAnimated()) {
            return 1;
        }

        if ($this->animationFrameCount !== null) {
            return $this->animationFrameCount;
        }

        $count = count($this->meta->getFrames());
        if ($count > 0) {
            $this->animationFrameCount = $count;
            return $this->animationFrameCount;
        }

        $width = $this->image->getImageWidth();
        $height = $this->image->getImageHeight();

        $cols = intval(round(16 / $this->meta->getWidth()));

        $frameWidth = $width * ($this->meta->getWidth() / 16);
        $frameHeight = $frameWidth * ($this->meta->getHeight() / 16);

        $this->animationFrameCount = intval(round($height / $frameHeight * $cols));

        return $this->animationFrameCount;
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     */
    public function getAnimationTickLength(): int
    {
        if ($this->animationTickLength !== null) {
            return $this->animationTickLength;
        }

        $count = count($this->meta->getFrames());
        if ($count === 0) {
            $this->animationTickLength = $this->getAnimationFrameCount() * $this->meta->getFrameTime();
            return $this->animationTickLength;
        }

        $tickLength = 0;
        foreach ($this->meta->getFrames() as $frame) {
            $tickLength += $frame->getTime();
        }
        $this->animationTickLength = $tickLength;
        return $this->animationTickLength;
    }

    /**
     * @inheritDoc
     * @param int $animationTick
     * @return Imagick
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public function getImage(int $animationTick = 0): Imagick
    {
        if (!$this->meta->isAnimated()) {
            return $this->image;
        }

        $frame = $this->resolveAnimationFrame($animationTick);
        $base = $this->getImageFrame($frame->getFrame());
        if(!$this->meta->isInterpolated()) {
            return $base;
        }

        $base = clone $base;
        $dest = $this->getImageFrame(($frame->getFrame() + 1) % $this->getAnimationFrameCount());
        ColorBlender::blendImages($base, $dest, $frame->getInterpolation());
        return $base;
    }

    /**
     * @param int $frame
     * @return Imagick
     * @throws ImagickException
     */
    protected function getImageFrame(int $frame): Imagick
    {
        if (isset($this->frameImages[$frame])) {
            return $this->frameImages[$frame];
        }

        $image = $this->cropImageFrame($frame);
        $this->frameImages[$frame] = $image;
        return $image;
    }

    /**
     * @return TextureMeta
     */
    public function getMeta(): TextureMeta
    {
        return $this->meta;
    }
}
