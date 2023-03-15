<?php

namespace Aternos\Renderchest\Tinter;

use ImagickPixel;

interface Tinterface
{
    /**
     * @param int $index
     * @return ImagickPixel|null
     */
    public function getTintColor(int $index): ?ImagickPixel;
}