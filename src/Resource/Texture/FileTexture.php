<?php

namespace Aternos\Renderchest\Resource\Texture;

use Imagick;
use ImagickException;

class FileTexture extends ImagickTexture
{
    /**
     * @param string $path
     * @param TextureMeta $meta
     * @throws ImagickException
     */
    public function __construct(string $path, TextureMeta $meta)
    {
        $image = new Imagick();
        $image->readImage($path);
        parent::__construct($image, $meta);
    }
}
