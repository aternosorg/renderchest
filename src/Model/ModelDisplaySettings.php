<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Vector\Matrix4;
use Aternos\Renderchest\Vector\Vector3;

class ModelDisplaySettings
{
    protected static ?self $defaultSettings = null;

    /**
     * @return static
     */
    public static function getDefault(): self
    {
        if (static::$defaultSettings === null) {
            static::$defaultSettings = new static(new Vector3(0, 0, 0), new Vector3(0, 0, 0), new Vector3(1, 1, 1));
        }
        return static::$defaultSettings;
    }

    /**
     * @param Vector3 $rotation
     * @param Vector3 $translation
     * @param Vector3 $scale
     */
    public function __construct(protected Vector3 $rotation, protected Vector3 $translation, protected Vector3 $scale)
    {
    }

    /**
     * @return Vector3
     */
    public function getRotation(): Vector3
    {
        return $this->rotation;
    }

    /**
     * @return Vector3
     */
    public function getTranslation(): Vector3
    {
        return $this->translation;
    }

    /**
     * @return Vector3
     */
    public function getScale(): Vector3
    {
        return $this->scale;
    }

    /**
     * @return Matrix4
     */
    public function asMatrix4(): Matrix4
    {
        $pivot = Vector3::center();
        return Matrix4::identity()
            ->translate(...$pivot->getValues())
            ->translate(...$this->translation->getValues())
            ->scale(...$this->scale->getValues())
            ->rotateZRadians(deg2rad(-$this->rotation->z))
            ->rotateXRadians(deg2rad($this->rotation->x))
            ->rotateYRadians(deg2rad($this->rotation->y))
            ->translate(...$pivot->clone()->multiply(-1)->getValues())
        ;
    }
}
