<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Model\LightSource\GuiFrontLight;
use Aternos\Renderchest\Model\LightSource\GuiSideLight;
use Aternos\Renderchest\Model\LightSource\LightSourceInterface;

enum ModelGuiLight: string
{
    case FRONT = "front";
    case SIDE = "side";

    /**
     * @return LightSourceInterface
     */
    public function getLightSource(): LightSourceInterface
    {
        return match ($this) {
            ModelGuiLight::SIDE => new GuiSideLight(),
            default => new GuiFrontLight(),
        };
    }
}
