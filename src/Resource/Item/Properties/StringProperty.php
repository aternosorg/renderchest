<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

use stdClass;

class StringProperty extends Property
{
    protected const DEFAULT = [
        "minecraft:main_hand" => "right",
        "minecraft:charge_type" => "none",
        "minecraft:trim_material" => "none",
        "minecraft:block_state" => "",
        "minecraft:display_context" => "gui",
        "minecraft:local_time" => "",
        "minecraft:context_dimension" => "minecraft:overworld",
        "minecraft:context_entity_type" => "minecraft:player",
        "minecraft:custom_model_data" => ""
    ];

    /**
     * @param string $name
     * @param string|null $value
     */
    public function __construct(
        string $name,
        protected ?string $value = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    public function get(stdClass $options): string
    {
        if ($this->value !== null) {
            return $this->value;
        }
        return static::DEFAULT[$this->name] ?? "";
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue(?string $value): static
    {
        $this->value = $value;
        return $this;
    }
}
