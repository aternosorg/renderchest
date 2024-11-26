<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use stdClass;

enum ItemType: string
{
    case Model = "minecraft:model";
    case Composite = "minecraft:composite";
    case Condition = "minecraft:condition";
    case Select = "minecraft:select";
    case RangeDispatch = "minecraft:range_dispatch";
    case BundleSelectedItem = "minecraft:bundle/selected_item";
    case Empty = "minecraft:empty";
    case Special = "minecraft:special";

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return ItemInterface
     * @throws InvalidItemDefinitionException
     */
    public static function createFromData(stdClass $data, ResourceManagerInterface $resourceManager): ItemInterface
    {
        $typeString = $data->type ?? null;
        if (!is_string($typeString)) {
            throw new InvalidItemDefinitionException("Item type must be a string");
        }
        $type = self::tryFrom($typeString);
        if ($type === null) {
            throw new InvalidItemDefinitionException("Invalid item type: " . $typeString);
        }
        return $type->create($data, $resourceManager);
    }

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return ItemInterface
     * @throws InvalidItemDefinitionException
     */
    public function create(stdClass $data, ResourceManagerInterface $resourceManager): ItemInterface
    {
        return match ($this) {
            self::Model => ModelItem::fromData($data, $resourceManager),
            self::Composite => CompositeItem::fromData($data, $resourceManager),
            self::Condition => ConditionItem::fromData($data, $resourceManager),
            self::Select => SelectItem::fromData($data, $resourceManager),
            self::RangeDispatch => RangeDispatchItem::fromData($data, $resourceManager),
            self::BundleSelectedItem => BundleSelectedItem::fromData($data, $resourceManager),
            self::Empty => EmptyItem::fromData($data, $resourceManager),
            self::Special => SpecialItem::fromData($data, $resourceManager)
        };
    }
}
