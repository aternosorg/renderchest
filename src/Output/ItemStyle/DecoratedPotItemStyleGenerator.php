<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class DecoratedPotItemStyleGenerator extends ItemStyleGenerator
{
    const SHARDS = [
        "brick",
        "pottery_shard_archer",
        "pottery_shard_arms_up",
        "pottery_shard_prize",
        "pottery_shard_skull"
    ];

    const STYLES = [
        "blank",
        "archer",
        "arms_up",
        "prize",
        "skull"
    ];

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return $item->getLocator() === "minecraft:decorated_pot";
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
        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                    "-webkit-mask-image" => "none"
                ]),
            (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted"))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                ])
        ];

        array_push($styles, ...$this->generateShardStyles(false));
        return $styles;
    }

    /**
     * @inheritDoc
     */
    public function getItemFallbackStyles(): array
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true),
                ]),
            (new PropertyListEntry($this->getCssSelector() . "." . $prefix . "enchanted"))
                ->setProperties([
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true),
                ])
        ];

        array_push($styles, ...$this->generateShardStyles(true));
        return $styles;
    }

    /**
     * @param bool $fallback
     * @return CSSEntry[]
     */
    protected function generateShardStyles(bool $fallback): array
    {
        $styles = [];
        for ($i = 0; $i < count(static::SHARDS); $i++) {
            for ($j = 0; $j < count(static::SHARDS); $j++) {
                $shard1 = static::SHARDS[$i];
                $shard2 = static::SHARDS[$j];
                $style1 = static::STYLES[$i];
                $style2 = static::STYLES[$j];

                $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getShardSelector($shard1, $shard2)))
                    ->setProperties([
                        "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_decorated_pot_" . $style1 . "_" . $style2, $fallback),
                    ]);
            }
        }
        return $styles;
    }

    protected function getShardSelector(string $shard1, string $shard2): string
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        return "." . $prefix . "pot-minecraft_" . $shard1 . "-minecraft_" . $shard2;
    }
}
