<?php

namespace Aternos\Renderchest\Vector;

use Aternos\Renderchest\Exception\InvalidTransformationException;
use InvalidArgumentException;

class Matrix4 {

    /**
     * @var float[] 16 elements in column-major order
     */
    protected array $m;

    /**
     * @return static
     */
    public static function identity(): static
    {
        return new static();
    }

    /**
     * @param array|object $data
     * @return static
     * @throws InvalidTransformationException
     */
    public static function fromData(array|object $data): static
    {
        if (is_array($data)) {
            if (!static::isNumericArrayOfLength($data, 16)) {
                throw new InvalidTransformationException("Matrix4 transformation array must have exactly 16 numeric elements.");
            }
            return new static($data);
        }

        $matrix = static::identity();
        if (isset($data->translation)) {
            if (!static::isNumericArrayOfLength($data->translation, 3)) {
                throw new InvalidTransformationException("Matrix4 translation must be an array of 3 numeric elements.");
            }
            $matrix = $matrix->translate(...$data->translation);
        }
        if (isset($data->left_rotation)) {
            if (!static::isNumericArrayOfLength($data->left_rotation, 4)) {
                throw new InvalidTransformationException("Matrix4 left_rotation must be an array of 4 numeric elements (quaternion).");
            }
            $matrix = $matrix->rotate(...$data->left_rotation);
        }
        if (isset($data->scale)) {
            if (!static::isNumericArrayOfLength($data->scale, 3)) {
                throw new InvalidTransformationException("Matrix4 scale must be an array of 3 numeric elements.");
            }
            $matrix = $matrix->scale(...$data->scale);
        }
        if (isset($data->right_rotation)) {
            if (!static::isNumericArrayOfLength($data->right_rotation, 4)) {
                throw new InvalidTransformationException("Matrix4 right_rotation must be an array of 4 numeric elements (quaternion).");
            }
            $matrix = $matrix->rotate(...$data->right_rotation);
        }
        return $matrix;
    }

    /**
     * @param mixed $arr
     * @param int $length
     * @return bool
     */
    protected static function isNumericArrayOfLength(mixed $arr, int $length): bool
    {
        if (!is_array($arr)) {
            return false;
        }
        if (count($arr) !== $length) {
            return false;
        }
        foreach ($arr as $v) {
            if (!is_float($v) && !is_int($v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param float[]|null $elements
     */
    public function __construct(?array $elements = null) {
        if ($elements !== null && count($elements) !== 16) {
            throw new InvalidArgumentException("Matrix4 requires exactly 16 elements.");
        }

        $this->m = $elements ?? [
            1.0, 0.0, 0.0, 0.0,
            0.0, 1.0, 0.0, 0.0,
            0.0, 0.0, 1.0, 0.0,
            0.0, 0.0, 0.0, 1.0
        ];
    }

    /**
     * Standard Matrix Multiplication (Returns NEW instance: Result = This * Other)
     *
     * @param Matrix4 $other
     * @return static
     */
    public function multiply(Matrix4 $other): static
    {
        $a = $this->m;
        $b = $other->getComponents();
        $res = array_fill(0, 16, 0.0);

        for ($i = 0; $i < 4; $i++) { // Row
            for ($j = 0; $j < 4; $j++) { // Column
                $res[$i + $j * 4] =
                    $a[$i + 0*4] * $b[0 + $j*4] +
                    $a[$i + 1*4] * $b[1 + $j*4] +
                    $a[$i + 2*4] * $b[2 + $j*4] +
                    $a[$i + 3*4] * $b[3 + $j*4];
            }
        }
        return new static($res);
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return static
     */
    public function translate(float $x, float $y, float $z): static
    {
        $translation = [
            1, 0, 0, 0,
            0, 1, 0, 0,
            0, 0, 1, 0,
            $x, $y, $z, 1
        ];
        return $this->multiply(new static($translation));
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return static
     */
    public function scale(float $x, float $y, float $z): static
    {
        $scale = [
            $x, 0, 0, 0,
            0, $y, 0, 0,
            0, 0, $z, 0,
            0, 0, 0, 1
        ];
        return $this->multiply(new static($scale));
    }

    /**
     * Converts a Quaternion [x, y, z, w] to a rotation matrix and multiplies
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @param float $w
     * @return static
     */
    public function rotate(float $x, float $y, float $z, float $w): static
    {
        $rm = array_fill(0, 16, 0.0);

        $rm[0] = 1 - 2 * ($y * $y + $z * $z);
        $rm[1] = 2 * ($x * $y + $z * $w);
        $rm[2] = 2 * ($x * $z - $y * $w);
        $rm[3] = 0;

        $rm[4] = 2 * ($x * $y - $z * $w);
        $rm[5] = 1 - 2 * ($x * $x + $z * $z);
        $rm[6] = 2 * ($y * $z + $x * $w);
        $rm[7] = 0;

        $rm[8] = 2 * ($x * $z + $y * $w);
        $rm[9] = 2 * ($y * $z - $x * $w);
        $rm[10] = 1 - 2 * ($x * $x + $y * $y);
        $rm[11] = 0;

        $rm[12] = 0; $rm[13] = 0; $rm[14] = 0; $rm[15] = 1;

        return $this->multiply(new static($rm));
    }

    /**
     * @param float $rotX
     * @param float $rotY
     * @param float $rotZ
     * @return $this
     */
    public function rotateEulerRadians(float $rotX, float $rotY, float $rotZ): self {
        // 1. Calculate half-angles
        $cx = cos($rotX * 0.5);
        $sx = sin($rotX * 0.5);
        $cy = cos($rotY * 0.5);
        $sy = sin($rotY * 0.5);
        $cz = cos($rotZ * 0.5);
        $sz = sin($rotZ * 0.5);

        // 2. Compute Quaternion components (YXZ Order)
        $qx = $sx * $cy * $cz + $cx * $sy * $sz;
        $qy = $cx * $sy * $cz - $sx * $cy * $sz;
        $qz = $cx * $cy * $sz - $sx * $sy * $cz;
        $qw = $cx * $cy * $cz + $sx * $sy * $sz;

        // 3. Pass to our existing rotate method
        return $this->rotate($qx, $qy, $qz, $qw);
    }

    /**
     * @param float $rotX
     * @param float $rotY
     * @param float $rotZ
     * @return $this
     */
    public function rotateEulerDegrees(float $rotX, float $rotY, float $rotZ): self {
        $radX = deg2rad($rotX);
        $radY = deg2rad($rotY);
        $radZ = deg2rad($rotZ);
        return $this->rotateEulerRadians($radX, $radY, $radZ);
    }

    public function rotateXRadians(float $rad): self {
        $c = cos($rad);
        $s = sin($rad);
        return $this->multiply(new self([
            1,  0,  0,  0,
            0,  $c, $s,  0,
            0, -$s, $c,  0,
            0,  0,  0,  1
        ]));
    }

    public function rotateYRadians(float $rad): self {
        $c = cos($rad);
        $s = sin($rad);
        return $this->multiply(new self([
            $c,  0, -$s,  0,
            0,   1,  0,   0,
            $s,  0,  $c,  0,
            0,   0,  0,   1
        ]));
    }

    public function rotateZRadians(float $rad): self {
        $c = cos($rad);
        $s = sin($rad);
        return $this->multiply(new self([
            $c, $s, 0, 0,
            -$s, $c, 0, 0,
            0,  0, 1, 0,
            0,  0, 0, 1
        ]));
    }

    /**
     * @return float[]
     */
    public function getComponents(): array
    {
        return $this->m;
    }

    /**
     * Applies the transformation matrix to a 3D point
     *
     * @param Vector3 $vector3 The input vector to transform
     * @return Vector3 The transformed vector
     */
    public function transformVector(Vector3 $vector3): Vector3
    {
        $m = $this->m;
        [$x, $y, $z] = $vector3->getValues();

        $nx = ($m[0] * $x) + ($m[4] * $y) + ($m[8] * $z) + $m[12];
        $ny = ($m[1] * $x) + ($m[5] * $y) + ($m[9] * $z) + $m[13];
        $nz = ($m[2] * $x) + ($m[6] * $y) + ($m[10] * $z) + $m[14];
        $nw = ($m[3] * $x) + ($m[7] * $y) + ($m[11] * $z) + $m[15];

        if ($nw !== 1.0 && $nw !== 0.0) {
            $nx /= $nw;
            $ny /= $nw;
            $nz /= $nw;
        }

        return new Vector3($nx, $ny, $nz);
    }
}
