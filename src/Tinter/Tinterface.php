<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceManagerInterface;
use ImagickPixel;
use stdClass;

interface Tinterface
{
    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return Tinterface|null
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): ?static;

    /**
     * @return ImagickPixel|null
     */
    public function getTintColor(): ?ImagickPixel;
}
