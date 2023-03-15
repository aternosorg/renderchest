<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Vector\Vector3;

class LightSource
{
    protected static ?self $frontLight = null;
    protected static ?self $sideLight = null;

    /**
     * @return static
     */
    static function getSideLight(): self
    {
        if (static::$sideLight === null) {
            static::$sideLight = new static(true, (new Vector3(0.75, -2, 0))->normalize(), 0.18);
        }
        return static::$sideLight;
    }

    /**
     * @return static
     */
    static function getFrontLight(): self
    {
        if (static::$frontLight === null) {
            static::$frontLight = new static(false, new Vector3(0, 0, 1), 1);
        }
        return static::$frontLight;
    }

    /**
     * @param bool $isActive
     * @param Vector3 $direction
     * @param float $baseLight
     */
    public function __construct(protected bool $isActive, protected Vector3 $direction, protected float $baseLight)
    {
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return LightSource
     */
    public function setIsActive(bool $isActive): LightSource
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Vector3|null
     */
    public function getDirection(): ?Vector3
    {
        return $this->direction;
    }

    /**
     * @param Vector3|null $direction
     * @return LightSource
     */
    public function setDirection(?Vector3 $direction): LightSource
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseLight(): float
    {
        return $this->baseLight;
    }

    /**
     * @param float $baseLight
     * @return LightSource
     */
    public function setBaseLight(float $baseLight): LightSource
    {
        $this->baseLight = $baseLight;
        return $this;
    }
}