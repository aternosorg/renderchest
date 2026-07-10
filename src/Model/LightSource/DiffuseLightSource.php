<?php

namespace Aternos\Renderchest\Model\LightSource;

use Aternos\Renderchest\Vector\Matrix4;
use Aternos\Renderchest\Vector\Vector3;

class DiffuseLightSource implements LightSourceInterface
{
    protected Vector3 $direction0;
    protected Vector3 $direction1;

    public function __construct(
        Vector3 $direction0,
        Vector3 $direction1,
        Matrix4 $transformation,
    )
    {
        $this->direction0 = $transformation->transformDirection($direction0->clone()->normalize());
        $this->direction1 = $transformation->transformDirection($direction1->clone()->normalize());
    }

    /**
     * @inheritDoc
     */
    public function getLightLevelForNormal(Vector3 $normal): float
    {
        return min(1.0, (max(0, Vector3::dotProduct($this->direction0, $normal)) +
                max(0, Vector3::dotProduct($this->direction1, $normal))) * 0.6 + 0.4);
    }
}
