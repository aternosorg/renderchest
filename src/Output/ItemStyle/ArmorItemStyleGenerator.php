<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;
use Aternos\Renderchest\Resource\DynamicResources\ArmorTrimModelGenerator;
use Aternos\Renderchest\Resource\DynamicResources\LeatherArmorTrimModelGenerator;

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

        "minecraft:golden_boots",
        "minecraft:golden_leggings",
        "minecraft:golden_chestplate",
        "minecraft:golden_helmet",

        "minecraft:diamond_boots",
        "minecraft:diamond_leggings",
        "minecraft:diamond_chestplate",
        "minecraft:diamond_helmet",

        "minecraft:netherite_boots",
        "minecraft:netherite_leggings",
        "minecraft:netherite_chestplate",
        "minecraft:netherite_helmet",
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
        $parts = explode("_", $name, 2);
        $armorMaterial = $parts[0];
        $armorType = $parts[1];
        if ($armorMaterial === "golden") {
            $armorMaterial = "gold";
        }

        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                ])
        ];

        foreach (Constants::TRIM_MATERIALS as $material) {
            $textureMaterial = $material;
            if ($textureMaterial == $armorMaterial) {
                $textureMaterial .= "_darker";
            }
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "trim-minecraft_" . $material . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()
                        ->getItemCSSUrl(ArmorTrimModelGenerator::getNamespace() . ":" . $armorType . "_trim_" . $textureMaterial, $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()
                        ->getItemCSSUrl(ArmorTrimModelGenerator::getNamespace() . ":" . $armorType . "_trim_" . $textureMaterial, $fallbackTexture),
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
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(LeatherArmorTrimModelGenerator::getNamespace() . ":" . $name . "_base", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallbackTexture),
                    "--" . $prefix . "layer-1-tint" => "#9e643f"
                ]),
            (new PropertyListEntry($this->getCssSelector() . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(LeatherArmorTrimModelGenerator::getNamespace() . ":" . $name . "_overlay", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl(LeatherArmorTrimModelGenerator::getNamespace() . ":" . $name . "_overlay", $fallbackTexture),
                ])
        ];

        foreach (Constants::TRIM_MATERIALS as $material) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "trim-minecraft_" . $material . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(LeatherArmorTrimModelGenerator::getNamespace() . ":" . $name . "_" . $material . "_trim", $fallbackTexture),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl(LeatherArmorTrimModelGenerator::getNamespace() . ":" . $name . "_" . $material . "_trim", $fallbackTexture),
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
