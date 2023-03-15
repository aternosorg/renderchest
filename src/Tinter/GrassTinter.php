<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceLocator;
use Exception;

class GrassTinter extends ColorMapTinter
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getColorMap(): ResourceLocator
    {
        return ResourceLocator::parse("minecraft:colormap/grass");
    }
}
