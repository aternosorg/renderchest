<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class ArmorItemStyleGenerator extends ItemStyleGenerator
{
    const LEATHER_ITEMS = [
        "minecraft:leather_boots",
        "minecraft:leather_leggings",
        "minecraft:leather_chestplate",
        "minecraft:leather_helmet",
    ];

    const ITEMS = [
        ...self::LEATHER_ITEMS,

        "minecraft:chainmail_boots",
        "minecraft:chainmail_leggings",
        "minecraft:chainmail_chestplate",
        "minecraft:chainmail_helmet",

        "minecraft:iron_boots",
        "minecraft:iron_leggings",
        "minecraft:iron_chestplate",
        "minecraft:iron_helmet",

        "minecraft:gold_boots",
        "minecraft:gold_leggings",
        "minecraft:gold_chestplate",
        "minecraft:gold_helmet",

        "minecraft:diamond_boots",
        "minecraft:diamond_leggings",
        "minecraft:diamond_chestplate",
        "minecraft:diamond_helmet",

        "minecraft:netherite_boots",
        "minecraft:netherite_leggings",
        "minecraft:netherite_chestplate",
        "minecraft:netherite_helmet",
    ];

    const TRIM_MATERIALS = [
        "quartz",
        "iron",
        "gold",
        "diamond",
        "netherite",
        "redstone",
        "copper",
        "emerald",
        "lapis",
        "amethyst",
    ];

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return in_array($item->getLocator(), static::ITEMS);
    }

    /**
     * @inheritDoc
     */
    public static function getGlobalStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param bool $fallbackTexture
     * @return CSSEntry[]
     */
    protected function createItemStyles(bool $fallbackTexture = false): array
    {
        if (in_array($this->item->getLocator(), static::LEATHER_ITEMS)) {
            return $this->getLeatherItemStyles($fallbackTexture);
        }

        $prefix = $this->item->getGenerator()->getPrefix();
        $name = substr($this->item->getLocator(), 10);
        $armorMaterial = explode("_", $name)[0];

        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                ])
        ];

        foreach (static::TRIM_MATERIALS as $material) {
            $textureMaterial = $material;
            if ($textureMaterial == $armorMaterial) {
                $textureMaterial .= "_darker";
            }
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "trim-minecraft_" . $material))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()
                        ->getItemCSSUrl($this->item->getLocator() . "_" . $textureMaterial . "_trim", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()
                        ->getItemCSSUrl($this->item->getLocator() . "_" . $textureMaterial . "_trim", $fallbackTexture),
                ]);
        }

        return $styles;
    }

    protected function getLeatherItemStyles(bool $fallbackTexture = false): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        $name = substr($this->item->getLocator(), 10);

        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_" . $name . "_base", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                    "--" . $prefix . "layer-1-tint" => "#9e643f"
                ]),
            (new PropertyListEntry($this->getCssSelector() . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_" . $name . "_overlay", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_" . $name . "_overlay", $fallbackTexture),
                ])
        ];

        foreach (static::TRIM_MATERIALS as $material) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "trim-minecraft_" . $material))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator() . "_" . $material . "_trim", $fallbackTexture),
                ]);

            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "trim-minecraft_" . $material . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_" . $name . "_" . $material . "_trim", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_" . $name . "_" . $material . "_trim", $fallbackTexture),
                ]);
        }

        return $styles;
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        return $this->createItemStyles();
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        return $this->createItemStyles(true);
    }
}