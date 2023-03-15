<?php

namespace Aternos\Renderchest\Resource\Texture;

use stdClass;

class TextureMeta
{
    /**
     * @param stdClass $data
     * @return static
     */
    static function fromTextureData(stdClass $data): static
    {
        $animation = $data->animation ?? null;
        $frameTime = $animation?->frametime ?? 1;
        $width = $animation?->width ?? 16;
        $height = $animation?->height ?? 16;
        $interpolate = $animation?->interpolate ?? false;

        $frames = [];
        foreach ($animation?->frames ?? [] as $frame) {
            if ($frame instanceof stdClass) {
                $frames[] = new TextureFrame($frame->index ?? 0, $frame->time ?? $frameTime);
            } else {
                $frames[] = new TextureFrame($frame, $frameTime);
            }
        }

        return new static(!!$animation, $width, $height, $frames, $frameTime, $interpolate);
    }

    /**
     * @param bool $animated
     * @param int $width
     * @param int $height
     * @param TextureFrame[] $frames
     * @param int $frameTime
     * @param bool $interpolate
     */
    public function __construct(protected bool $animated = false, protected int $width = 16,
                                protected int  $height = 16, protected array $frames = [],
                                protected int $frameTime = 1, protected bool $interpolate = false)
    {
    }

    /**
     * @return bool
     */
    public function isInterpolated(): bool
    {
        return $this->interpolate;
    }

    /**
     * @return int
     */
    public function getFrameTime(): int
    {
        return $this->frameTime;
    }

    /**
     * @return bool
     */
    public function isAnimated(): bool
    {
        return $this->animated;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return TextureFrame[]
     */
    public function getFrames(): array
    {
        return $this->frames;
    }
}
