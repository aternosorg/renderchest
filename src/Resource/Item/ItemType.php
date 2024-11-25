<?php

namespace Aternos\Renderchest\Resource\Item;

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
}
