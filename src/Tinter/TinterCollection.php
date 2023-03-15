<?php

namespace Aternos\Renderchest\Tinter;

use ImagickPixel;

class TinterCollection implements Tinterface
{
    /**
     * @var Tinterface[]
     */
    protected array $tinters = [];

    /**
     * @param Tinterface $tinter
     * @return $this
     */
    public function addTinter(Tinterface $tinter): static
    {
        array_unshift($this->tinters, $tinter);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTintColor(int $index): ?ImagickPixel
    {
        foreach ($this->tinters as $tinter) {
            $tint = $tinter->getTintColor($index);
            if ($tint !== null) {
                return $tint;
            }
        }
        return null;
    }
}
