<?php

namespace Aternos\Renderchest\Tinter;

use ImagickPixel;

class TinterList
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
        $this->tinters[] = $tinter;
        return $this;
    }

    /**
     * @param Tinterface[] $tinters
     * @return $this
     */
    public function setTinters(array $tinters): static
    {
        $this->tinters = $tinters;
        return $this;
    }

    /**
     * @param int $index
     * @return ImagickPixel|null
     */
    public function getTintColor(int $index): ?ImagickPixel
    {
        $tinter = $this->tinters[$index] ?? null;
        return $tinter?->getTintColor();
    }
}
