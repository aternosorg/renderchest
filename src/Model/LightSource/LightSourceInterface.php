<?php

namespace Aternos\Renderchest\Model\LightSource;

use Aternos\Renderchest\Vector\Vector3;

interface LightSourceInterface
{
    /**
     * Get the light level (between 0 and 1) for a face using its normal vector
     *
     * @param Vector3 $normal
     * @return float
     */
    public function getLightLevelForNormal(Vector3 $normal): float;
}
