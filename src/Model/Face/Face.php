<?php

namespace Aternos\Renderchest\Model\Face;

use Aternos\Renderchest\Model\LightSource;
use Aternos\Renderchest\Model\Rasterizer\Point;
use Aternos\Renderchest\Model\Rasterizer\TriangleRasterizer;
use Aternos\Renderchest\Util\ColorBlender;
use Aternos\Renderchest\Vector\UV;
use Aternos\Renderchest\Vector\Vector3;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIteratorException;

class Face
{
    /**
     * @param Vector3 $v0
     * @param Vector3 $v1
     * @param Vector3 $v2
     * @param Vector3 $v3
     * @param FaceInfo $faceInfo
     * @param LightSource $lightSource
     */
    public function __construct(
        protected Vector3     $v0,
        protected Vector3     $v1,
        protected Vector3     $v2,
        protected Vector3     $v3,
        protected FaceInfo    $faceInfo,
        protected LightSource $lightSource)
    {
    }

    /**
     * @return Vector3|null
     */
    public function getNormal(): ?Vector3
    {
        $v0to2 = $this->v2->clone()->subtract($this->v0);
        $v0to1 = $this->v1->clone()->subtract($this->v0);

        $p = Vector3::crossProduct(
            $v0to2,
            $v0to1
        );

        if ($p->getLength() === 0.0) {
            return null;
        }
        return $p->normalize();
    }

    protected function getProjectedVertices(int $width, int $height): array
    {
        $v1p = $this->v0->project($width, $height);
        $v2p = $this->v1->project($width, $height);
        $v3p = $this->v2->project($width, $height);
        $v4p = $this->v3->project($width, $height);

        return [$v1p, $v2p, $v3p, $v4p];
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $animationTick
     * @return FaceImage|null
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public function getPerspectiveImage(int $width, int $height, int $animationTick = 0): ?FaceImage
    {
        $uv1 = $this->faceInfo->getUv1()?->clone();
        $uv2 = $this->faceInfo->getUv2()?->clone();

        $normUv1 = $uv1->clone()->divide(16);
        $normUv2 = $uv2->clone()->divide(16);
        $normUvWidth = $normUv2->u - $normUv1->u;
        $normUvHeight = $normUv2->v - $normUv1->v;

        if ($normUvHeight === 0.0 || $normUvWidth === 0.0) {
            return null;
        }

        $vps = $this->getProjectedVertices($width, $height);

        $normal = $this->getNormal();
        if ($normal === null || Vector3::dotProduct(new Vector3(0, 0, -1), $normal) >= -0.01) {
            return null;
        }

        $baseTexture = clone $this->faceInfo->getTexture()->getImage($animationTick);

        if ($baseTexture->getImageType() !== Imagick::IMGTYPE_TRUECOLORMATTE) {
            $baseTexture->transformImageColorspace(Imagick::COLORSPACE_UNDEFINED);
        }

        $baseWidth = $baseTexture->getImageWidth();
        $baseHeight = $baseTexture->getImageHeight();

        $absUv1 = $normUv1->clone()->multiplyByVector(new UV($baseWidth, $baseHeight));
        $absUv2 = $normUv2->clone()->multiplyByVector(new UV($baseWidth, $baseHeight));

        if ($this->faceInfo->getTintIndex() !== null && $this->faceInfo->getTinter()) {
            $color = $this->faceInfo->getTinter()->getTintColor($this->faceInfo->getTintIndex());
            if ($color !== null) {
                ColorBlender::tintImage($baseTexture, $color);
            }
        }

        if ($this->lightSource->isActive()) {
            $this->darkenTexture(
                $baseTexture,
                max(0,
                    (Vector3::dotProduct($normal, $this->lightSource->getDirection()) + 1) / 2 -
                    $this->lightSource->getBaseLight()),
                $absUv1,
                $absUv2
            );
        }

        $uvs = [
            new UV($normUv1->u, $normUv1->v),
            new UV($normUv1->u + $normUvWidth, $normUv1->v),
            new UV($normUv1->u + $normUvWidth, $normUv1->v + $normUvHeight),
            new UV($normUv1->u, $normUv1->v + $normUvHeight)
        ];

        $rotations = floor($this->faceInfo->getRotation() / 90);
        for ($i = 0; $i < $rotations; $i++) {
            $uv = array_pop($uvs);
            array_unshift($uvs, $uv);
        }

        $color = new Imagick();
        $color->newImage($width, $height, 'transparent');
        $colorIterator = $color->getPixelIterator();

        $depth = new Imagick();
        $depth->newImage($width, $height, 'transparent');
        $depthIterator = $depth->getPixelIterator();

        TriangleRasterizer::drawTexturedTriangle(
            new Point($vps[0], $uvs[0], abs($this->v0->z / 20)),
            new Point($vps[2], $uvs[2], abs($this->v2->z / 20)),
            new Point($vps[3], $uvs[3], abs($this->v3->z / 20)),
            $colorIterator,
            $depthIterator,
            $baseTexture
        );
        TriangleRasterizer::drawTexturedTriangle(
            new Point($vps[0], $uvs[0], abs($this->v0->z / 20)),
            new Point($vps[1], $uvs[1], abs($this->v1->z / 20)),
            new Point($vps[2], $uvs[2], abs($this->v2->z / 20)),
            $colorIterator,
            $depthIterator,
            $baseTexture
        );

        return new FaceImage($color, $depth);
    }

    /**
     * @return FaceInfo
     */
    public function getFaceInfo(): FaceInfo
    {
        return $this->faceInfo;
    }

    /**
     * @param Imagick $texture
     * @param float $amount
     * @param UV $from
     * @param UV $to
     * @return void
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    protected function darkenTexture(Imagick $texture, float $amount, UV $from, UV $to): void
    {
        $startX = $from->u;
        $startY = $from->v;
        $endX = $to->u;
        $endY = $to->v;
        if($startX > $endX) {
            $startX = $to->u;
            $endX = $from->u;
        }
        if($startY > $endY) {
            $startY = $to->v;
            $endY = $from->v;
        }

        $baseBlendColor = new ImagickPixel('black');
        $iterator = $texture->getPixelIterator();
        foreach ($iterator as $row => $pixelRow) {
            if($row < $startY - 1) {
                continue;
            }
            if($row > $endY + 1) {
                break;
            }
            for($i = $startX - 1; $i <= $endX + 1; $i++) {
                $pixel = $pixelRow[$i] ?? null;
                if($pixel === null) {
                    continue;
                }
                $baseBlendColor->setColorValue(Imagick::COLOR_ALPHA, $amount * $pixel->getColorValue(Imagick::COLOR_ALPHA));
                ColorBlender::blendColors($pixel, $baseBlendColor);
            }
            $iterator->syncIterator();
        }
    }


    /**
     * @return float
     */
    public function getMinZ(): float
    {
        return min($this->v0->z, $this->v1->z, $this->v2->z, $this->v3->z);
    }

    /**
     * @return float
     */
    public function getMaxZ(): float
    {
        return max($this->v0->z, $this->v1->z, $this->v2->z, $this->v3->z);
    }
}
