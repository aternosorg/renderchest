<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class PotionItemStyleGenerator extends ItemStyleGenerator
{
    protected const EMPTY_POTION_COLOR = "#f800f8";

    protected const POTION_ITEMS = [
        "minecraft:potion" => [
            "minecraft:rc_potion_content",
            "minecraft:rc_potion_bottle",
            "minecraft:potion"
        ],
        "minecraft:splash_potion" => [
            "minecraft:rc_potion_content",
            "minecraft:rc_potion_bottle_splash",
            "minecraft:splash_potion"
        ],
        "minecraft:lingering_potion" => [
            "minecraft:rc_potion_content",
            "minecraft:rc_potion_bottle_lingering",
            "minecraft:lingering_potion"
        ],
        "minecraft:tipped_arrow" => [
            "minecraft:rc_tipped_arrow_head",
            "minecraft:rc_tipped_arrow_base",
            "minecraft:tipped_arrow"
        ]
    ];

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return in_array($item->getLocator(), array_keys(static::POTION_ITEMS));
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        [$content, $bottle, $mask] = static::POTION_ITEMS[$this->item->getLocator()];

        return [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($content),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($mask),
                    "--" . $prefix . "layer-1-tint" => static::EMPTY_POTION_COLOR
                ]),
            (new PropertyListEntry($this->getCssSelector() . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($bottle),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($bottle),
                ])
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        [$content, $bottle, $mask] = static::POTION_ITEMS[$this->item->getLocator()];

        return [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($content, true),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($mask, true),
                ]),
            (new PropertyListEntry($this->getCssSelector() . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($bottle, true),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($bottle, true),
                ])
        ];
    }
}
