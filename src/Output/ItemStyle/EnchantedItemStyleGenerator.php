<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\AnimationEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class EnchantedItemStyleGenerator extends ItemStyleGenerator
{
    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalStyles(ItemLibraryGenerator $generator): array
    {
        $prefix = $generator->getPrefix();
        return [
            (new AnimationEntry($prefix . "enchanted"))
                ->addKeyframe((new PropertyListEntry("from"))->setProperty("background-position", "-256px 1024px"))
                ->addKeyframe((new PropertyListEntry("to"))->setProperty("background-position", "256px -1024px"))
        ];
    }

    public static function getGlobalPostStyles(ItemLibraryGenerator $generator): array
    {
        $prefix = $generator->getPrefix();
        $enchantedSelectors = array_map(fn($s) => $s . ":after", [...$generator->getEnchantmentSelectors(), "." . $prefix . "enchanted"]);
        return [
            (new PropertyListEntry(implode(", ", $enchantedSelectors)))
                ->setProperties([
                    "position" => "absolute",
                    "width" => "200%",
                    "height" => "200%",
                    "top" => "-50%",
                    "left" => "-50%",
                    "image-rendering" => "pixelated",
                    "mix-blend-mode" => "screen",
                    "content" => "''",
                    "opacity" => 0.6,
                    "background-image" => $generator->getEnchantmentUrl(),
                    "background-size" => "512px 512px",
                    "filter" => "blur(2px) contrast(200%)",
                    "transform" => "rotate(-8deg)",

                    "animation-name" => $prefix . "enchanted",
                    "animation-duration" => "32s",
                    "animation-timing-function" => "linear",
                    "animation-iteration-count" => "infinite"
                ])
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalPostFallbackStyles(ItemLibraryGenerator $generator): array
    {
        $prefix = $generator->getPrefix();
        $enchantedSelectors = array_map(fn($s) => $s . ":after", [...$generator->getEnchantmentSelectors(), "." . $prefix . "enchanted"]);
        return [
            (new PropertyListEntry(implode(", ", $enchantedSelectors)))
                ->setProperties([
                    "background-image" => $generator->getEnchantmentUrl(true)
                ])
        ];
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        return [];
    }
}