<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\CSS\PropertyListEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;
use Aternos\Renderchest\Resource\DynamicResources\DecoratedPotModelGenerator;

class DecoratedPotItemStyleGenerator extends ItemStyleGenerator
{
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
        foreach (Constants::POTTERY_SHERDS as $sherd) {
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getSherdSelector($sherd, 1)))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(DecoratedPotModelGenerator::getNamespace() . ":pot_b_" . $sherd, $fallback),
                ]);
            $styles[] = (new PropertyListEntry($this->getCssSelector() . $this->getSherdSelector($sherd, 2) . ":before"))
                ->setProperties([
                    "background-image" => $this->item->getGenerator()->getItemCSSUrl(DecoratedPotModelGenerator::getNamespace() . ":pot_o_" . $sherd, $fallback),
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
