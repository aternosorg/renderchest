<?php

namespace Aternos\Renderchest\Model\LightSource;

use Aternos\Renderchest\Vector\Matrix4;
use Aternos\Renderchest\Vector\Vector3;

class GuiFrontLight extends DiffuseLightSource
{
    public function __construct()
    {
        parent::__construct(
            new Vector3(0.2, 1.0, -0.7),
            new Vector3(-0.2, 1.0, 0.7),
            Matrix4::identity()
                ->scale(1.0, -1.0, 1.0)
                ->rotateYRadians(-M_PI / 8)
                ->rotateXRadians(M_PI * 3.0 / 4.0)
        );
    }
}
