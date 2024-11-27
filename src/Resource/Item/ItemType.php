<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
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
     * @param Properties|null $properties
     * @return ItemInterface
     * @throws InvalidItemDefinitionException
     */
    public static function createFromData(stdClass $data, ResourceManagerInterface $resourceManager, ?Properties $properties = null): ItemInterface
    {
        $typeString = $data->type ?? null;
        if (!is_string($typeString)) {
            throw new InvalidItemDefinitionException("Item type must be a string");
        }
        $type = self::tryFrom($typeString);
        if ($type === null) {
            throw new InvalidItemDefinitionException("Invalid item type: " . $typeString);
        }
        return $type->create($data, $resourceManager, $properties);
    }

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @param Properties|null $properties
     * @return ItemInterface
     * @throws InvalidItemDefinitionException
     */
    public function create(stdClass $data, ResourceManagerInterface $resourceManager, ?Properties $properties = null): ItemInterface
    {
        $properties = $properties ?? new Properties();
        return match ($this) {
            self::Model => ModelItem::fromData($data, $resourceManager, $properties),
            self::Composite => CompositeItem::fromData($data, $resourceManager, $properties),
            self::Condition => ConditionItem::fromData($data, $resourceManager, $properties),
            self::Select => SelectItem::fromData($data, $resourceManager, $properties),
            self::RangeDispatch => RangeDispatchItem::fromData($data, $resourceManager, $properties),
            self::BundleSelectedItem => BundleSelectedItem::fromData($data, $resourceManager, $properties),
            self::Empty => EmptyItem::fromData($data, $resourceManager, $properties),
            self::Special => SpecialItem::fromData($data, $resourceManager, $properties)
        };
    }
}
