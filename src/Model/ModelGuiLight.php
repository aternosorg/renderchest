<?php

namespace Aternos\Renderchest\Model;

enum ModelGuiLight: string
{
    case FRONT = "front";
    case SIDE = "side";

    /**
     * @return LightSource
     */
    public function getLightSource(): LightSource
    {
        return match ($this) {
            ModelGuiLight::SIDE => LightSource::getSideLight(),
            default => LightSource::getFrontLight()
        };
    }
}
