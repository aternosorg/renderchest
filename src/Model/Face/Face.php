<?php

namespace Aternos\Renderchest\Model\Face;

use Aternos\Renderchest\Model\LightSource;
use Aternos\Renderchest\Model\Rasterizer\Point;
use Aternos\Renderchest\Model\Rasterizer\TriangleRasterizer;
use Aternos\Renderchest\Tinter\TinterList;
use Aternos\Renderchest\Util\ColorBlender;
use Aternos\Renderchest\Vector\Matrix4;
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
     * @param Vector3 $v0
     * @param Vector3 $v1
     * @param Vector3 $v2
     * @return Vector3|null
     */
    public function getNormal(Vector3 $v0, Vector3 $v1, Vector3 $v2): ?Vector3
    {
        $v0to2 = $v2->clone()->subtract($v0);
        $v0to1 = $v1->clone()->subtract($v0);

        $p = Vector3::crossProduct(
            $v0to2,
            $v0to1
        );

        if ($p->getLength() === 0.0) {
            return null;
        }
        return $p->normalize();
    }

    protected function getProjectedVertices(int $width, int $height, Vector3 $v0, Vector3 $v1, Vector3 $v2, Vector3 $v3): array
    {
        $v1p = $v0->project($width, $height);
        $v2p = $v1->project($width, $height);
        $v3p = $v2->project($width, $height);
        $v4p = $v3->project($width, $height);

        return [$v1p, $v2p, $v3p, $v4p];
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $animationTick
     * @param Matrix4|null $transformation
     * @param TinterList|null $tinters
     * @return FaceImage|null
     * @throws ImagickException
     * @throws ImagickPixelException
     * @throws ImagickPixelIteratorException
     */
    public function render(
        int $width,
        int $height,
        int $animationTick = 0,
        ?Matrix4 $transformation = null,
        ?TinterList $tinters = null
    ): ?FaceImage
    {
        $transformation = $transformation ?? Matrix4::identity();

        $normUv1 = $this->faceInfo->getUv1()?->clone();
        $normUv2 = $this->faceInfo->getUv2()?->clone();
        $normUvWidth = $normUv2->u - $normUv1->u;
        $normUvHeight = $normUv2->v - $normUv1->v;

        if ($normUvHeight === 0.0 || $normUvWidth === 0.0) {
            return null;
        }

        $v0 = $transformation->transformVector($this->v0);
        $v1 = $transformation->transformVector($this->v1);
        $v2 = $transformation->transformVector($this->v2);
        $v3 = $transformation->transformVector($this->v3);

        $vps = $this->getProjectedVertices($width, $height, $v0, $v1, $v2, $v3);

        $normal = $this->getNormal($v0, $v1, $v2);
        if ($normal === null || Vector3::dotProduct(new Vector3(0, 0, -1), $normal) >= -0.01) {
            return null;
        }

        $baseTexture = clone $this->faceInfo->getTexture()->getImage($animationTick);

        $baseWidth = $baseTexture->getImageWidth();
        $baseHeight = $baseTexture->getImageHeight();

        $absUv1 = $normUv1->clone()->multiplyByVector(new UV($baseWidth, $baseHeight));
        $absUv2 = $normUv2->clone()->multiplyByVector(new UV($baseWidth, $baseHeight));

        if ($this->faceInfo->getTintIndex() !== null && $tinters !== null) {
            $color = $tinters->getTintColor($this->faceInfo->getTintIndex());
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
            new Point($vps[0], $uvs[0], $this->linearDepth($v0->z)),
            new Point($vps[2], $uvs[2], $this->linearDepth($v2->z)),
            new Point($vps[3], $uvs[3], $this->linearDepth($v3->z)),
            $colorIterator,
            $depthIterator,
            $baseTexture
        );
        TriangleRasterizer::drawTexturedTriangle(
            new Point($vps[0], $uvs[0], $this->linearDepth($v0->z)),
            new Point($vps[1], $uvs[1], $this->linearDepth($v1->z)),
            new Point($vps[2], $uvs[2], $this->linearDepth($v2->z)),
            $colorIterator,
            $depthIterator,
            $baseTexture
        );

        return new FaceImage($color, $depth);
    }

    /**
     * @param float $z
     * @param float $zNear
     * @param float $zFar
     * @return float
     */
    protected function linearDepth(float $z, float $zNear = -1.0, float $zFar = 2.0): float
    {
        return ($z - $zNear) / ($zFar - $zNear);
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
