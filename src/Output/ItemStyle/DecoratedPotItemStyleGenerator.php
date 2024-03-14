<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class DecoratedPotItemStyleGenerator extends ItemStyleGenerator
{
    const SHERDS = [
        "brick",
        "angler_pottery_sherd",
        "archer_pottery_sherd",
        "arms_up_pottery_sherd",
        "blade_pottery_sherd",
        "brewer_pottery_sherd",
        "burn_pottery_sherd",
        "danger_pottery_sherd",
        "explorer_pottery_sherd",
        "friend_pottery_sherd",
        "heart_pottery_sherd",
        "heartbreak_pottery_sherd",
        "howl_pottery_sherd",
        "miner_pottery_sherd",
        "mourner_pottery_sherd",
        "plenty_pottery_sherd",
        "prize_pottery_sherd",
        "sheaf_pottery_sherd",
        "shelter_pottery_sherd",
        "skull_pottery_sherd",
        "snort_pottery_sherd",
        "flow_pottery_sherd",
        "guster_pottery_sherd",
        "scrape_pottery_sherd"
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

        array_push($styles, ...$this->generateSherdStyles(false));
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

        array_push($styles, ...$this->generateSherdStyles(true));
        return $styles;
    }

    /**
     * @param bool $fallback
     * @return CSSEntry[]
     */
    protected function generateSherdStyles(bool $fallback): array
    {
        $styles = [];
        foreach (static::SHERDS as $sherd) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getSherdSelector($sherd, 1)))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_pot_b_" . $sherd, $fallback),
                ]);
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getSherdSelector($sherd, 2) . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl("minecraft:rc_pot_o_" . $sherd, $fallback),
                ]);
        }
        return $styles;
    }

    /**
     * @param string $sherd
     * @param int $layer
     * @return string
     */
    protected function getSherdSelector(string $sherd, int $layer): string
    {
        $prefix = $this->item->getGenerator()->getPrefix();
        return "." . $prefix . "pot-" . $layer . "-minecraft_" . $sherd;
    }
}
