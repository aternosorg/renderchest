<?php

namespace Aternos\Renderchest\Output;

use Imagick;
use ImagickException;

class Item
{
    /**
     * @param string $locator
     * @param string $format
     * @return string
     */
    public static function getItemImageFilePath(string $locator, string $format): string
    {
        return "items/" . str_replace(":", "/", $locator) .
            "." . $format;
    }

    /**
     * @param Imagick $image
     * @param string $locator
     * @param string $format
     * @param string $output
     * @param bool $createPngFallback
     * @return void
     * @throws ImagickException
     */
    public static function writeImageFile(Imagick $image, string $locator, string $format, string $output, bool $createPngFallback): void
    {
        $image->setFormat($format);
        $image->setOption('webp:lossless', 'true');

        $path = $output . "/" . static::getItemImageFilePath($locator, $format);
        if (!file_exists(dirname($path))) {
            @mkdir(dirname($path), recursive: true);
        }
        $image->writeImages($path, true);

        if ($createPngFallback) {
            $image->setFormat("png");
            file_put_contents($output . "/" . static::getItemImageFilePath($locator, "png"), $image);
        }
    }

    /**
     * @param string $locator
     * @param ItemLibraryGenerator $generator
     */
    public function __construct(
        protected string               $locator,
        protected ItemLibraryGenerator $generator
    )
    {
    }

    /**
     * @param string|null $format
     * @return string
     */
    public function getImageFilePath(?string $format = null): string
    {
        return static::getItemImageFilePath($this->locator, $format ?? $this->generator->getFormat());
    }

    /**
     * @return string
     */
    public function getLocator(): string
    {
        return $this->locator;
    }

    /**
     * @return ItemLibraryGenerator
     */
    public function getGenerator(): ItemLibraryGenerator
    {
        return $this->generator;
    }
}
