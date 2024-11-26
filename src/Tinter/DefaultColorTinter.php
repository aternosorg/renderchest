<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use stdClass;

class DefaultColorTinter extends ConstantTinter
{

    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        $value = $data->default ?? null;
        return static::fromColorValue($value);
    }
}
