<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class MapItemStyleGenerator extends ItemStyleGenerator
{
    const DEFAULT_MAP_COLOR = "#46402e";
    const BASE = "minecraft:rc_filled_map_base";
    const OVERLAY = "minecraft:rc_filled_map_overlay";
    const MASK = "minecraft:filled_map";

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return $item->getLocator() === "minecraft:filled_map";
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param bool $fallback
     * @return CSSEntry[]
     */
    protected function getStyles(bool $fallback): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        return [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(static::BASE, $fallback),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl(static::MASK, $fallback),
                    "--" . $prefix . "layer-2-tint" => static::DEFAULT_MAP_COLOR
                ]),
            (new PropertyListEntry($this->getCssSelector() . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(static::OVERLAY, $fallback),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl(static::OVERLAY, $fallback),
                ])
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        return $this->getStyles(false);
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        return $this->getStyles(true);
    }
}
