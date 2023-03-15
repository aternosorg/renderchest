<?php

namespace Aternos\Renderchest\Util;

use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIteratorException;

class ColorBlender
{
    /**
     * @param ImagickPixel $dest
     * @param ImagickPixel $source
     * @return void
     * @throws ImagickPixelException
     */
    public static function blendColors(ImagickPixel $dest, ImagickPixel $source): void
    {
        $sr = $source->getColorValue(Imagick::COLOR_RED);
        $sg = $source->getColorValue(Imagick::COLOR_GREEN);
        $sb = $source->getColorValue(Imagick::COLOR_BLUE);
        $sa = $source->getColorValue(Imagick::COLOR_ALPHA);

        $dr = $dest->getColorValue(Imagick::COLOR_RED);
        $dg = $dest->getColorValue(Imagick::COLOR_GREEN);
        $db = $dest->getColorValue(Imagick::COLOR_BLUE);
        $da = $dest->getColorValue(Imagick::COLOR_ALPHA);

        $ra = 1 - (1 - $sa) * (1 - $da);
        if ($ra === 0.0) {
            $rr = 0;
            $rg = 0;
            $rb = 0;
        } else {
            $rr = ($sr * $sa / $ra) + ($dr * $da * (1 - $sa) / $ra);
            $rg = ($sg * $sa / $ra) + ($dg * $da * (1 - $sa) / $ra);
            $rb = ($sb * $sa / $ra) + ($db * $da * (1 - $sa) / $ra);
        }

        $dest->setColorValue(Imagick::COLOR_RED, $rr);
        $dest->setColorValue(Imagick::COLOR_GREEN, $rg);
        $dest->setColorValue(Imagick::COLOR_BLUE, $rb);
        $dest->setColorValue(Imagick::COLOR_ALPHA, $ra);
    }

    /**
     * @param ImagickPixel|null $dest
     * @param ImagickPixel|null $source
     * @return ImagickPixel|null
     * @throws ImagickPixelException
     */
    public static function blendCopyColors(?ImagickPixel $dest, ?ImagickPixel $source): ?ImagickPixel
    {
        if($source === null) {
            return $dest ?? null;
        }
        if($dest === null) {
            return $source;
        }

        $newDest = new ImagickPixel();
        $newDest->setColorFromPixel($dest);
        static::blendColors($newDest, $source);
        return $newDest;
    }

    /**
     * @param Imagick $image
     * @param ImagickPixel $color
     * @return void
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public static function tintImage(Imagick $image, ImagickPixel $color): void
    {
        $blendColor = new ImagickPixel('black');

        $iterator = $image->getPixelIterator();
        foreach ($iterator as $pixelRow) {
            /** @var ImagickPixel $pixel */
            foreach ($pixelRow as $pixel) {
                $value = $pixel->getColorValue(Imagick::COLOR_RED);
                $blendColor->setColorValue(Imagick::COLOR_RED, $color->getColorValue(Imagick::COLOR_RED) * $value);
                $blendColor->setColorValue(Imagick::COLOR_GREEN, $color->getColorValue(Imagick::COLOR_GREEN) * $value);
                $blendColor->setColorValue(Imagick::COLOR_BLUE, $color->getColorValue(Imagick::COLOR_BLUE) * $value);
                $blendColor->setColorValue(Imagick::COLOR_ALPHA, $pixel->getColorValue(Imagick::COLOR_ALPHA));
                ColorBlender::blendColors($pixel, $blendColor);
            }
            $iterator->syncIterator();
        }
    }

    /**
     * @param Imagick $dest
     * @param Imagick $source
     * @param float $alpha
     * @return Imagick
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public static function blendImages(Imagick $dest, Imagick $source, float $alpha): Imagick
    {
        $destIterator = $dest->getPixelIterator();
        $sourceIterator = $source->getPixelIterator();

        while ($destIterator->valid() && $sourceIterator->valid()) {
            $destRow = $destIterator->current();
            $sourceRow = $sourceIterator->current();
            foreach ($destRow as $x => $destPixel) {
                $sourcePixel = new ImagickPixel();
                $sourcePixel->setColorFromPixel($sourceRow[$x]);
                $sourcePixel->setColorValue(Imagick::COLOR_ALPHA, $sourcePixel->getColorValue(Imagick::COLOR_ALPHA) * $alpha);
                ColorBlender::blendColors($destPixel, $sourcePixel);
            }
            $destIterator->syncIterator();
            $sourceIterator->syncIterator();

            $destIterator->next();
            $sourceIterator->next();
        }

        return $dest;
    }
}
