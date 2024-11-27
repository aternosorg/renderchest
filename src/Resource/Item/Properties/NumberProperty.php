<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

use stdClass;

class NumberProperty extends Property
{
    protected const DEFAULT = [
        "minecraft:bundle/fullness" => 0,
        "minecraft:damage" => 0,
        "minecraft:count" => 0,
        "minecraft:cooldown" => 0,
        "minecraft:time" => 0,
        "minecraft:compass" => 0,
        "minecraft:crossbow/pull" => 0,
        "minecraft:use_duration" => 0,
        "minecraft:use_cycle" => 0,
        "minecraft:custom_model_data" => 0
    ];

    /**
     * @param string $name
     * @param float|null $value
     */
    public function __construct(
        string $name,
        protected ?float $value = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    public function get(stdClass $options): float
    {
        if ($this->value !== null) {
            return $this->value;
        }
        return static::DEFAULT[$this->name] ?? 0;
    }

    /**
     * @param float|null $value
     * @return $this
     */
    public function setValue(?float $value): static
    {
        $this->value = $value;
        return $this;
    }
}
