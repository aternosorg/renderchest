<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class ChargedProjectileItemStyleGenerator extends ItemStyleGenerator
{
    const PROJECTILES = [
        "arrow" => "arrow",
        "spectral_arrow" => "arrow",
        "tipped_arrow" => "arrow",
        "firework_rocket" => "firework"
    ];

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return $item->getLocator() === "minecraft:crossbow";
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
    protected function generateStyles(bool $fallback = false): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), $fallback),
                    "-webkit-mask-image" => "none"
                ]),
            (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted"))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                ])
        ];

        foreach (static::PROJECTILES as $projectile => $texture) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "projectile-minecraft_" . $projectile))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator() . "_" . $texture, $fallback),
                    "-webkit-mask-image" => "none"
                ]);
            $styles[] = (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted" . "." . $prefix . "projectile-minecraft_" . $projectile))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator() . "_" . $texture, $fallback)
                ]);
        }
        return $styles;
    }

    /**
     * @inheritDoc
     */
    public function getItemStyles(): array
    {
        return $this->generateStyles();
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        return $this->generateStyles(true);
    }
}
