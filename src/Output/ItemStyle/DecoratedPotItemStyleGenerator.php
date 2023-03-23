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
        "angler_pottery_shard",
        "archer_pottery_shard",
        "arms_up_pottery_shard",
        "blade_pottery_shard",
        "brewer_pottery_shard",
        "burn_pottery_shard",
        "danger_pottery_shard",
        "explorer_pottery_shard",
        "friend_pottery_shard",
        "heart_pottery_shard",
        "heartbreak_pottery_shard",
        "howl_pottery_shard",
        "miner_pottery_shard",
        "mourner_pottery_shard",
        "plenty_pottery_shard",
        "prize_pottery_shard",
        "sheaf_pottery_shard",
        "shelter_pottery_shard",
        "skull_pottery_shard",
        "snort_pottery_shard"
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
        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator()),
                    "-webkit-mask-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator())
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
        $styles = [
            (new PropertyListEntry($this->getCssSelector()))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl($this->item->getLocator(), true),
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
        foreach (static::SHARDS as $shard) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getShardSelector($shard, 1)))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_pot_b_" . $shard, $fallback),
                ]);
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getShardSelector($shard, 2) . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_pot_o_" . $shard, $fallback),
                ]);
        }
        return $styles;
    }

    /**
     * @param string $shard
     * @param int $layer
     * @return string
     */
    protected function getShardSelector(string $shard, int $layer): string
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        return "." . $prefix . "pot-" . $layer . "-minecraft_" . $shard;
    }
}
