<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Exception;
use stdClass;

class GrassTinter extends ColorMapTinter
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        $temperature = isset($data->temperature) && is_numeric($data->temperature) ?? 0.5;
        $downfall = isset($data->downfall) && is_numeric($data->downfall) ?? 1;

        $x = 1.0 - ($downfall * $temperature);
        $y = 1.0 - $temperature;

        return (new static($resourceManager))->setSamplePosition($x, $y);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getColorMap(): ResourceLocator
    {
        return ResourceLocator::parse("minecraft:colormap/grass");
    }
}
