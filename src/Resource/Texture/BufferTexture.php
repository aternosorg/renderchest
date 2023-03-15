<?php

namespace Aternos\Renderchest\Resource\Texture;

use Imagick;
use ImagickException;

class BufferTexture extends ImagickTexture
{
    /**
     * @param string $data
     * @param TextureMeta $meta
     * @throws ImagickException
     */
    public function __construct(string $data, protected TextureMeta $meta)
    {
        $image = new Imagick();
        $image->readImageBlob($data);
        parent::__construct($image, $meta);
    }
}