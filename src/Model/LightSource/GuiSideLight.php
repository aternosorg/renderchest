<?php

namespace Aternos\Renderchest\Model\LightSource;

use Aternos\Renderchest\Vector\Matrix4;
use Aternos\Renderchest\Vector\Vector3;

class GuiSideLight extends DiffuseLightSource
{
    public function __construct()
    {
        parent::__construct(
            new Vector3(0.2, 1.0, -0.7),
            new Vector3(-0.2, 1.0, 0.7),
            Matrix4::identity()
                ->rotateEulerRadians(3.2375858, 1.0821041, 0.0)
                ->rotateEulerRadians(M_PI * 3.0 / 4.0, -M_PI / 8, 0.0)
        );
    }
}
