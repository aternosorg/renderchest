<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use stdClass;

class ConditionItem implements ItemInterface
{
    const PROPERTIES = [
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
        "minecraft:custom_model_data" => false
    ];

    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        if (!isset($data->property) || !is_string($data->property)) {
            throw new InvalidItemDefinitionException("Condition item must have a property");
        }

        if (!isset($data->on_true) || !($data->on_true instanceof stdClass)) {
            throw new InvalidItemDefinitionException("Condition item must have an on_true object");
        }

        if (!isset($data->on_false) || !($data->on_false instanceof stdClass)) {
            throw new InvalidItemDefinitionException("Condition item must have an on_false object");
        }

        $trueItem = ItemType::createFromData($data->on_true, $resourceManager);
        $falseItem = ItemType::createFromData($data->on_false, $resourceManager);
        return new static($trueItem, $falseItem, $data->property);
    }

    /**
     * @param ItemInterface $trueItem
     * @param ItemInterface $falseItem
     * @param string $property
     */
    public function __construct(
        protected ItemInterface $trueItem,
        protected ItemInterface $falseItem,
        protected string $property
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        if (isset(static::PROPERTIES[$this->property]) && static::PROPERTIES[$this->property]) {
            return $this->trueItem->render($width, $height);
        } else {
            return $this->falseItem->render($width, $height);
        }
    }
}
