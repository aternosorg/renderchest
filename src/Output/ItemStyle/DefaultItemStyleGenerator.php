<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class DefaultItemStyleGenerator extends ItemStyleGenerator
{
    const ENCHANTED_EFFECT_ITEMS = [
        "minecraft:experience_bottle",
        "minecraft:enchanted_golden_apple",
        "minecraft:enchanted_book",
        "minecraft:written_book"
    ];

    /**
     * @param ItemLibraryGenerator $generator
     * @return string[]
     */
    public static function getEnchantmentSelectors(ItemLibraryGenerator $generator): array
    {
        $selectors = [];
        foreach (static::ENCHANTED_EFFECT_ITEMS as $itemName) {
            $selectors[] = static::getItemCssSelector($itemName, $generator);
        }
        return $selectors;
    }

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalStyles(ItemLibraryGenerator $generator): array
    {
        $prefix = $generator->getPrefix();
        $style = (new PropertyListEntry("." . $generator->getPrefix() . "item"))
            ->setProperties([
                "position" => "relative",
                "image-rendering" => "pixelated",
                "background-image" => $generator->getItemCSSUrl("minecraft:unknown"),
                "-webkit-mask-image" => $generator->getItemCSSUrl("minecraft:unknown"),
                "background-size" => "cover",
                "background-color" => static::cssVar("layer-1-tint", $prefix),
                "background-blend-mode" => "multiply",
                "-webkit-mask-size" => "cover",
            ]);

        $enchanted = (new PropertyListEntry("." . $prefix . "item" . "." . $prefix . "enchanted"))
            ->setProperties([
                "-webkit-mask-image" => $generator->getItemCSSUrl("minecraft:unknown"),
            ]);

        $before = (new PropertyListEntry("." . $generator->getPrefix() . "item:before"))
            ->setProperties([
                "content" => "''",
                "position" => "absolute",
                "width" => "100%",
                "height" => "100%",
                "top" => "0",
                "left" => "0",
                "image-rendering" => "pixelated",
                "background-image" => $generator->getItemCSSUrl("minecraft:empty"),
                "background-size" => "cover",
                "background-color" => static::cssVar("layer-2-tint", $prefix),
                "background-blend-mode" => "multiply",
                "-webkit-mask-size" => "cover",
            ]);

        return [$style, $enchanted, $before];
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalFallbackStyles(ItemLibraryGenerator $generator): array
    {
        $style = (new PropertyListEntry("." . $generator->getPrefix() . "item"))
            ->setProperties([
                "background-image" => $generator->getItemCSSUrl("minecraft:unknown", true)
            ]);

        $before = (new PropertyListEntry("." . $generator->getPrefix() . "item:before"))
            ->setProperties([
                "background-image" => $generator->getItemCSSUrl("minecraft:empty", true),
            ]);

        return [$style, $before];
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        if(str_starts_with($this->item->getLocator(), "minecraft:rc_")) {
            return [];
        }
        $prefix = $this->item->getGenerator()->getPrefix();
        $hasMask = in_array($this->item->getLocator(), static::ENCHANTED_EFFECT_ITEMS);
        return [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                    "-webkit-mask-image" => $hasMask ? $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()) : "none",
                ]),
            (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted"))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                ])
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        $hasMask = in_array($this->item->getLocator(), static::ENCHANTED_EFFECT_ITEMS);
        return [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true),
                    "-webkit-mask-image" => $hasMask ? $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true) : "none",
                ]),
            (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted"))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true),
                ])
        ];
    }
}
