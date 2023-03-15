<?php

namespace Aternos\Renderchest\Model\Face;

use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\Tinterface;
use Aternos\Renderchest\Vector\UV;
use Exception;
use stdClass;

class FaceInfo
{
    /**
     * @param stdClass $data
     * @param TextureList $textures
     * @param Tinterface|null $tinter
     * @return FaceInfo
     * @throws Exception
     */
    public static function fromModelData(stdClass $data, TextureList $textures, ?Tinterface $tinter): FaceInfo
    {
        $uvData = $data->uv ?? [];
        $uv1 = count($uvData) >= 2 ? new UV(...array_slice($uvData, 0, 2)) : null;
        $uv2 = count($uvData) >= 4 ? new UV(...array_slice($uvData, 2, 2)) : null;

        $texture = $textures->getResolvable($data->texture);

        $rotation = $data->rotation ?? 0;
        $tintIndex = $data->tintindex ?? null;

        return new static($uv1, $uv2, $texture, $rotation, $tintIndex, $tinter);
    }

    /**
     * @param UV|null $uv1
     * @param UV|null $uv2
     * @param TextureInterface $texture
     * @param int $rotation
     * @param int|null $tintIndex
     * @param Tinterface|null $tinter
     */
    public function __construct(
        protected ?UV         $uv1,
        protected ?UV         $uv2,
        protected TextureInterface $texture,
        protected int              $rotation,
        protected ?int             $tintIndex,
        protected ?Tinterface      $tinter
    )
    {
    }

    /**
     * @return UV|null
     */
    public function getUv1(): ?UV
    {
        return $this->uv1;
    }

    /**
     * @return UV|null
     */
    public function getUv2(): ?UV
    {
        return $this->uv2;
    }

    /**
     * @return TextureInterface
     */
    public function getTexture(): TextureInterface
    {
        return $this->texture;
    }

    /**
     * @return int
     */
    public function getRotation(): int
    {
        return $this->rotation;
    }

    /**
     * @param UV|null $uv1
     * @return $this
     */
    public function setUv1(?UV $uv1): static
    {
        $this->uv1 = $uv1;
        return $this;
    }

    /**
     * @param UV|null $uv2
     * @return $this
     */
    public function setUv2(?UV $uv2): static
    {
        $this->uv2 = $uv2;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTintIndex(): ?int
    {
        return $this->tintIndex;
    }

    /**
     * @return Tinterface|null
     */
    public function getTinter(): ?Tinterface
    {
        return $this->tinter;
    }
}

