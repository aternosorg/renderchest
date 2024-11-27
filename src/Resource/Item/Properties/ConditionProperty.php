<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

use stdClass;

class ConditionProperty extends Property
{
    protected const DEFAULT = [
        "minecraft:using_item" => false,
        "minecraft:broken" => false,
        "minecraft:damaged" => false,
        "minecraft:has_component" => false,
        "minecraft:fishing_rod/cast" => false,
        "minecraft:bundle/has_selected_item" => false,
        "minecraft:selected" => false,
        "minecraft:carried" => false,
        "minecraft:extended_view" => false,
        "minecraft:keybind_down" => false,
        "minecraft:view_entity" => true,
        "minecraft:custom_model_data" => false,
    ];

    /**
     * @param string $name
     * @param bool $value
     */
    public function __construct(
        string $name,
        protected ?bool $value = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    public function get(stdClass $options): bool
    {
        if ($this->value !== null) {
            return $this->value;
        }
        return static::DEFAULT[$this->name] ?? false;
    }

    /**
     * @param bool|null $value
     * @return $this
     */
    public function setValue(?bool $value): static
    {
        $this->value = $value;
        return $this;
    }
}
