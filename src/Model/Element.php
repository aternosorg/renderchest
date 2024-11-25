<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Model\Face\Face;
use Aternos\Renderchest\Model\Face\FaceDirection;
use Aternos\Renderchest\Model\Face\FaceInfo;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\Tinterface;
use Aternos\Renderchest\Vector\UV;
use Aternos\Renderchest\Vector\Vector3;
use Exception;
use stdClass;

class Element
{
    protected Vector3 $b;
    protected Vector3 $c;
    protected Vector3 $d;
    protected Vector3 $e;
    protected Vector3 $f;
    protected Vector3 $h;

    /**
     * @var Face[]
     */
    protected array $faces = [];

    /**
     * @param stdClass $data
     * @param ModelGuiLight $light
     * @param TextureList $textures
     * @param ModelDisplaySettings $displaySettings
     * @param Tinterface|null $tinter
     * @return Element
     * @throws Exception
     */
    public static function fromModelData(
        stdClass    $data, ModelGuiLight $light,
        TextureList $textures, ModelDisplaySettings $displaySettings
    ): Element
    {
        $from = new Vector3(...$data->from);
        $to = new Vector3(...$data->to);
        $faces = [];
        foreach ($data->faces ?? [] as $name => $face) {
            $faces[$name] = FaceInfo::fromModelData($face, $textures);
        }

        $shade = !isset($data->shade) || $data->shade;
        $element = new static($to, $from, $faces, $shade ? $light->getLightSource() : LightSource::getFrontLight());

        $rotation = $data->rotation_rc ?? $data->rotation ?? null;
        if ($rotation) {
            $element->rotate(new Vector3(...$rotation->origin), Axis::from($rotation->axis), $rotation->angle / 180 * pi());
        }
        $element->scale($displaySettings->getScale());
        $element->rotate(Vector3::center(), Axis::Y, $displaySettings->getRotation()->y / 180 * pi());
        $element->rotate(Vector3::center(), Axis::X, $displaySettings->getRotation()->x / 180 * pi());
        $element->rotate(Vector3::center(), Axis::Z, -$displaySettings->getRotation()->z / 180 * pi());
        $element->translate($displaySettings->getTranslation());

        return $element;
    }

    /**
     * @param Vector3 $a
     * @param Vector3 $g
     * @param FaceInfo[] $faces
     * @param LightSource $lightSource
     */
    public function __construct(protected Vector3 $a, protected Vector3 $g, array $faces, protected LightSource $lightSource)
    {
        $this->b = new Vector3($this->g->x, $this->a->y, $this->a->z);
        $this->c = new Vector3($this->g->x, $this->g->y, $this->a->z);
        $this->d = new Vector3($this->a->x, $this->g->y, $this->a->z);

        $this->e = new Vector3($this->a->x, $this->a->y, $this->g->z);
        $this->f = new Vector3($this->g->x, $this->a->y, $this->g->z);
        $this->h = new Vector3($this->a->x, $this->g->y, $this->g->z);

        foreach ($faces as $direction => $faceInfo) {
            $dir = FaceDirection::from($direction);
            $this->createFaceUVs($dir, $faceInfo);
            $this->faces[] = $this->createFace($dir, $faceInfo);
        }
    }

    /**
     * @param FaceDirection $direction
     * @param FaceInfo $faceInfo
     * @return void
     */
    protected function createFaceUVs(FaceDirection $direction, FaceInfo $faceInfo): void
    {
        if (!$faceInfo->getUv1()) {
            $faceInfo->setUv1($this->getAutomaticUV1($direction));
        }
        if (!$faceInfo->getUv2()) {
            $faceInfo->setUv2($this->getAutomaticUV2($direction));
        }
    }

    /**
     * @param FaceDirection $direction
     * @return UV
     */
    protected function getAutomaticUV1(FaceDirection $direction): UV
    {
        return match ($direction) {
            FaceDirection::DOWN => new UV($this->c->x, $this->c->z),
            FaceDirection::UP => new UV($this->f->x, $this->f->z),
            FaceDirection::NORTH => new UV($this->g->x, $this->g->y),
            FaceDirection::SOUTH => new UV($this->c->x, $this->c->y),
            FaceDirection::WEST => new UV($this->g->z, $this->g->y),
            FaceDirection::EAST => new UV($this->h->z, $this->h->y)
        };
    }

    /**
     * @param FaceDirection $direction
     * @return UV
     */
    protected function getAutomaticUV2(FaceDirection $direction): UV
    {
        return match ($direction) {
            FaceDirection::DOWN => new UV($this->h->x, $this->h->z),
            FaceDirection::UP => new UV($this->a->x, $this->a->z),
            FaceDirection::NORTH => new UV($this->e->x, $this->e->y),
            FaceDirection::SOUTH => new UV($this->a->x, $this->a->y),
            FaceDirection::WEST => new UV($this->b->z, $this->b->y),
            FaceDirection::EAST => new UV($this->a->z, $this->a->y)
        };
    }

    /**
     * @param FaceDirection $direction
     * @param FaceInfo $info
     * @return Face
     */
    protected function createFace(FaceDirection $direction, FaceInfo $info): Face
    {
        return match ($direction) {
            FaceDirection::DOWN => new Face($this->c, $this->d, $this->h, $this->g, $info, $this->lightSource),
            FaceDirection::UP => new Face($this->f, $this->e, $this->a, $this->b, $info, $this->lightSource),
            FaceDirection::NORTH => new Face($this->e, $this->f, $this->g, $this->h, $info, $this->lightSource),
            FaceDirection::SOUTH => new Face($this->b, $this->a, $this->d, $this->c, $info, $this->lightSource),
            FaceDirection::WEST => new Face($this->f, $this->b, $this->c, $this->g, $info, $this->lightSource),
            FaceDirection::EAST => new Face($this->a, $this->e, $this->h, $this->d, $info, $this->lightSource)
        };
    }

    /**
     * @param Vector3 $by
     * @return $this
     */
    public function scale(Vector3 $by): static
    {
        $center = new Vector3(8, 8, 8);
        foreach ($this->getVertices() as $v) {
            $v->subtract($center)->multiplyByVector($by)->add($center);
        }
        return $this;
    }

    /**
     * @param Vector3 $by
     * @return $this
     */
    public function translate(Vector3 $by): static
    {
        foreach ($this->getVertices() as $v) {
            $v->add($by);
        }
        return $this;
    }

    /**
     * @param Vector3 $base
     * @param Axis $axis
     * @param float $angle
     * @return $this
     */
    public function rotate(Vector3 $base, Axis $axis, float $angle): static
    {
        foreach ($this->getVertices() as $v) {
            $v->subtract($base)->rotate($axis, $angle)->add($base);
        }
        return $this;
    }

    /**
     * @return Vector3[]
     */
    public function getVertices(): array
    {
        return [
            $this->a, $this->b, $this->c, $this->d,
            $this->e, $this->f, $this->g, $this->h
        ];
    }

    /**
     * @return Face[]
     */
    public function getFaces(): array
    {
        return $this->faces;
    }

    /**
     * @return LightSource
     */
    public function getLightSource(): LightSource
    {
        return $this->lightSource;
    }
}
