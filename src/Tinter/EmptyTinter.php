<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickPixel;
use stdClass;

class EmptyTinter implements Tinterface
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): ?static
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function getTintColor(): ?ImagickPixel
    {
        return null;
    }
}
