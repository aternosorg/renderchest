<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickPixel;
use stdClass;

class ConstantTinter implements Tinterface
{
    protected ImagickPixel $color;

    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): ?static
    {
        $value = $data->value ?? null;
        return static::fromColorValue($value);
    }

    /**
     * @param mixed $value
     * @return static|null
     */
    public static function fromColorValue(mixed $value): ?static
    {
        if (is_int($value)) {
            $r = $value & 0xff;
            $g = ($value >> 8) & 0xff;
            $b = ($value >> 16) & 0xff;
            $a = ($value >> 24) & 0xff;
            return static::fromRGBA($r, $g, $b, $a);
        } else if (is_array($value)) {
            return static::fromArray($value);
        }

        return null;
    }

    /**
     * @param array $color
     * @return static|null
     */
    public static function fromArray(array $color): ?static
    {
        $r = $color[0] ?? null;
        $g = $color[1] ?? null;
        $b = $color[2] ?? null;
        if (!is_numeric($r) || !is_numeric($g) || !is_numeric($b)) {
            return null;
        }
        $r *= 0xff;
        $g *= 0xff;
        $b *= 0xff;
        return static::fromRGBA($r, $g, $b);
    }

    /**
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $a
     * @return static
     */
    public static function fromRGBA(int $r, int $g, int $b, int $a = 0xff): static
    {
        $a /= 0xff;
        return new static(new ImagickPixel("rgba($r, $g, $b, $a)"));
    }

    /**
     * @param ImagickPixel $color
     */
    public function __construct(ImagickPixel $color)
    {
        $this->color = $color;
    }

    /**
     * @inheritDoc
     */
    public function getTintColor(): ?ImagickPixel
    {
        return $this->color;
    }
}
