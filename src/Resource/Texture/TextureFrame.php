<?php

namespace Aternos\Renderchest\Resource\Texture;

class TextureFrame
{
    public function __construct(protected int $index, protected int $time)
    {
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }
}