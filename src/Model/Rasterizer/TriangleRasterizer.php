<?php

namespace Aternos\Renderchest\Model\Rasterizer;

use Aternos\Renderchest\Vector\UV;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIterator;
use ImagickPixelIteratorException;

class TriangleRasterizer
{
    /**
     * @param Point $p1
     * @param Point $p2
     * @param Point $p3
     * @param ImagickPixelIterator $colorIterator
     * @param ImagickPixelIterator $depthIterator
     * @param Imagick $texture
     * @return void
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public static function drawTexturedTriangle(
        Point $p1, Point $p2, Point $p3,
        ImagickPixelIterator $colorIterator, ImagickPixelIterator $depthIterator,
        Imagick $texture
    ): void
    {
        $textureWidth = $texture->getImageWidth();
        $textureHeight = $texture->getImageHeight();

        $points = [$p1, $p2, $p3];
        usort($points, function(Point $a, Point $b) {
            return $a->getVector()->y <=> $b->getVector()->y;
        });
        [$p1, $p2, $p3] = $points;

        $vectorDiff1 = $p2->getVector()->clone()->subtract($p1->getVector());
        $uvDiff1 = $p2->getUv()->clone()->subtract($p1->getUv());
        $depthDiff1 = $p2->getDepth() - $p1->getDepth();

        $vectorDiff2 = $p3->getVector()->clone()->subtract($p1->getVector());
        $uvDiff2 = $p3->getUv()->clone()->subtract($p1->getUv());
        $depthDiff2 = $p3->getDepth() - $p1->getDepth();

        $xyStep1 = $vectorDiff1->y ? ($vectorDiff1->x / $vectorDiff1->y) : 0;
        $xyStep2 = $vectorDiff2->y ? ($vectorDiff2->x / $vectorDiff2->y) : 0;
        $uvStep1 = $vectorDiff1->y ? new UV($uvDiff1->u / abs($vectorDiff1->y), $uvDiff1->v / abs($vectorDiff1->y)) : new UV(0, 0);
        $uvStep2 = $vectorDiff2->y ? new UV($uvDiff2->u / abs($vectorDiff2->y), $uvDiff2->v / abs($vectorDiff2->y)) : new UV(0, 0);
        $depthStep1 = $vectorDiff1->y ? ($depthDiff1 / abs($vectorDiff1->y)) : 0;
        $depthStep2 = $vectorDiff2->y ? ($depthDiff2 / abs($vectorDiff2->y)) : 0;

        $startPoint = $p1;
        $endPoint = $p2;

        for($part = 0; $part < 2; $part++) {
            if($vectorDiff1->y > 0) {
                $startY = $startPoint->getVector()->y;
                $endY = $endPoint->getVector()->y;

                static::moveIterator($colorIterator, $startY);
                static::moveIterator($depthIterator, $startY);
                for($y = $startY; $y <= $endY; $y++) {
                    if($y < 0) {
                        continue;
                    }
                    if(!$colorIterator->valid() || !$depthIterator->valid()) {
                        break;
                    }

                    $startX = round($startPoint->getVector()->x + ($y - $startPoint->getVector()->y) * $xyStep1);
                    $endX = round($p1->getVector()->x + ($y - $p1->getVector()->y) * $xyStep2);

                    $startU = $startPoint->getUv()->u + ($y - $startPoint->getVector()->y) * $uvStep1->u;
                    $startV = $startPoint->getUv()->v + ($y - $startPoint->getVector()->y) * $uvStep1->v;
                    $startDepth = $startPoint->getDepth() + ($y - $startPoint->getVector()->y) * $depthStep1;

                    $endU = $p1->getUv()->u + ($y - $p1->getVector()->y) * $uvStep2->u;
                    $endV = $p1->getUv()->v + ($y - $p1->getVector()->y) * $uvStep2->v;
                    $endDepth = $p1->getDepth() + ($y - $p1->getVector()->y) * $depthStep2;

                    if($startX > $endX) {
                        [$startX, $endX] = [$endX, $startX];
                        [$startU, $endU] = [$endU, $startU];
                        [$startV, $endV] = [$endV, $startV];
                        [$startDepth, $endDepth] = [$endDepth, $startDepth];
                    }

                    $lineLength = $endX - $startX;
                    $textureStep = $lineLength ? 1.0 / $lineLength : 0;

                    //slightly move into the first step to prevent seams
                    $textureX = $textureStep * 0.3;

                    /** @var ImagickPixel[] $colorRow */
                    $colorRow = $colorIterator->current();
                    /** @var ImagickPixel[] $depthRow */
                    $depthRow = $depthIterator->current();

                    for($x = $startX - 1; $x <= $endX + 1; $x++) {
                        $currentU = (1.0 - $textureX) * $startU + $textureX * $endU;
                        $currentV = (1.0 - $textureX) * $startV + $textureX * $endV;
                        $currentDepth = (1.0 - $textureX) * $startDepth + $textureX * $endDepth;

                        if(isset($colorRow[$x])) {
                            $colorRow[$x]->setColorFromPixel($texture->getImagePixelColor($currentU * $textureWidth, $currentV * $textureHeight));
                        }

                        if($x >= $startX && $x < $endX && isset($depthRow[$x])) {
                            $depthColor = $depthRow[$x];
                            $depthColor->setColorValue(Imagick::COLOR_ALPHA, 1);
                            $depthColor->setColorValue(Imagick::COLOR_RED, $currentDepth);

                            $textureX += $textureStep;
                        }
                    }
                    $colorIterator->syncIterator();
                    $depthIterator->syncIterator();
                    $colorIterator->next();
                    $depthIterator->next();
                }
            }

            $vectorDiff1 = $p3->getVector()->clone()->subtract($p2->getVector());
            $uvDiff1 = $p3->getUv()->clone()->subtract($p2->getUv());
            $depthDiff1 = $p3->getDepth() - $p2->getDepth();

            $xyStep1 = $vectorDiff1->y ? ($vectorDiff1->x / $vectorDiff1->y) : 0;
            $xyStep2 = $vectorDiff2->y ? ($vectorDiff2->x / $vectorDiff2->y) : 0;

            $uvStep1 = $vectorDiff1->y ?
                new UV($uvDiff1->u / abs($vectorDiff1->y), $uvDiff1->v / abs($vectorDiff1->y)) :
                new UV(0, 0);

            $depthStep1 = $vectorDiff1->y ? ($depthDiff1 / abs($vectorDiff1->y)) : 0;

            $startPoint = $p2;
            $endPoint = $p3;
        }
    }

    /**
     * @param ImagickPixelIterator $iterator
     * @param int $row
     * @return void
     * @throws ImagickPixelIteratorException
     */
    protected static function moveIterator(ImagickPixelIterator $iterator, int $row): void
    {
        if($iterator->getIteratorRow() > $row) {
            $iterator->rewind();
        }
        $start = $iterator->getIteratorRow();
        for($i = 0; $i < $row - $start; $i++) {
            $iterator->next();
        }
    }
}

