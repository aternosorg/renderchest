<?php

namespace Aternos\Renderchest\Vector;

use Aternos\Renderchest\Model\Axis;

class Vector3 extends Vector
{
    const PROJECTION_SCALE = 16;

    /**
     * @param Vector3 $a
     * @param Vector3 $b
     * @return Vector3
     */
    static function crossProduct(Vector3 $a, Vector3 $b): Vector3
    {
        return new Vector3(
            $a->y * $b->z - $a->z * $b->y,
            $a->z * $b->x - $a->x * $b->z,
            $a->x * $b->y - $a->y * $b->x
        );
    }

    /**
     * @param Vector3 $a
     * @param Vector3 $b
     * @return float
     */
    static function dotProduct(Vector3 $a, Vector3 $b): float
    {
        return $a->x * $b->x + $a->y * $b->y + $a->z * $b->z;
    }

    static function center(): static
    {
        return new Vector3(8, 8, 8);
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     */
    public function __construct(public float $x, public float $y, public float $z)
    {
    }

    /**
     * @param int $width
     * @param int $height
     * @return Vector2
     */
    public function project(int $width, int $height): Vector2
    {
        $xC = $this->x - 8;
        $yC = $this->y - 8;
        return new Vector2(
            round($xC / static::PROJECTION_SCALE * $width + $width / 2),
            round($height - ($yC / static::PROJECTION_SCALE * $height + $height / 2))
        );
    }

    /**
     * @param float $angle
     * @return $this
     */
    public function rotateX(float $angle): static
    {
        $x = $this->x;
        $y = $this->y * cos($angle) - $this->z * sin($angle);
        $z = $this->y * sin($angle) + $this->z * cos($angle);

        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    /**
     * @param float $angle
     * @return $this
     */
    public function rotateY(float $angle): static
    {
        $x = $this->x * cos($angle) + $this->z * sin($angle);
        $y = $this->y;
        $z = -$this->x * sin($angle) + $this->z * cos($angle);

        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    /**
     * @param Axis $axis
     * @param float $angle
     * @return $this
     */
    public function rotate(Axis $axis, float $angle): static
    {
        return match ($axis) {
            Axis::X => $this->rotateX($angle),
            Axis::Y => $this->rotateY($angle),
            Axis::Z => $this->rotateZ($angle)
        };
    }

    /**
     * @param float $angle
     * @return $this
     */
    public function rotateZ(float $angle): static
    {
        $x = $this->x * cos($angle) - $this->y * sin($angle);
        $y = $this->x * sin($angle) + $this->y * cos($angle);
        $z = $this->z;

        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    /**
     * @return array
     */
    function getValues(): array
    {
        return [$this->x, $this->y, $this->z];
    }

    /**
     * @param array $values
     * @return void
     */
    function setValues(array $values): void
    {
        [$this->x, $this->y, $this->z] = $values;
    }
}
