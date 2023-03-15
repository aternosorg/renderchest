<?php

namespace Aternos\Renderchest\Vector;

abstract class Vector
{
    protected int $i = 0;

    /**
     * @return float[]
     */
    abstract function getValues(): array;

    /**
     * @param float[] $values
     * @return void
     */
    abstract function setValues(array $values): void;

    /**
     * @return float
     */
    public function getLength(): float
    {
        $sum = 0;
        foreach ($this->getValues() as $val) {
            $sum += pow($val, 2);
        }
        return sqrt($sum);
    }

    /**
     * @return $this
     */
    public function normalize(): static
    {
        return $this->divide($this->getLength());
    }

    /**
     * @param float $by
     * @return $this
     */
    public function divide(float $by): static
    {
        $values = [];
        foreach ($this->getValues() as $val) {
            $values[] = $val / $by;
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @return $this
     */
    public function round(): static
    {
        $values = [];
        foreach ($this->getValues() as $val) {
            $values[] = round($val);
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @param float $by
     * @return $this
     */
    public function multiply(float $by): static
    {
        $values = [];
        foreach ($this->getValues() as $val) {
            $values[] = $val * $by;
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @param Vector $a
     * @return $this
     */
    public function add(Vector $a): static
    {
        $values = [];
        foreach ($this->getValues() as $i => $val) {
            $values[] = $val + $a->getValues()[$i];
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @param Vector $a
     * @return $this
     */
    public function subtract(Vector $a): static
    {
        $values = [];
        foreach ($this->getValues() as $i => $val) {
            $values[] = $val - $a->getValues()[$i];
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @param Vector $a
     * @return $this
     */
    public function grow(Vector $a): static
    {
        $values = [];
        foreach ($this->getValues() as $i => $val) {
            if ($val < 0) {
                $values[] = $val - $a->getValues()[$i];
            } else {
                $values[] = $val + $a->getValues()[$i];
            }
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @param Vector $by
     * @return $this
     */
    public function multiplyByVector(Vector $by): static
    {
        $values = [];
        foreach ($this->getValues() as $i => $val) {
            $values[] = $val * $by->getValues()[$i];
        }
        $this->setValues($values);
        return $this;
    }

    /**
     * @return static
     */
    public function clone(): static
    {
        return new static(...$this->getValues());
    }
}
