<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\CSS\CSSEntry;
use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

abstract class ItemStyleGenerator
{
    /**
     * @param string $locator
     * @param ItemLibraryGenerator $generator
     * @return string
     */
    protected static function getItemCssSelector(string $locator, ItemLibraryGenerator $generator): string
    {
        $name = str_replace(":", "_", $locator);
        $prefix = $generator->getPrefix();
        return "." . $prefix . "item." . $prefix . $name;
    }

    /**
     * @param string $name
     * @param string $prefix
     * @return string
     */
    protected static function cssVar(string $name, string $prefix): string
    {
        return "var(--" . $prefix . $name . ")";
    }

    /**
     * @param Item $item
     * @return bool
     */
    abstract public static function hasItemStyle(Item $item): bool;

    /**
     * @param ItemLibraryGenerator $generator
     * @return string[]
     */
    public static function getEnchantmentSelectors(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param ItemLibraryGenerator $generator
     * @return CSSEntry[]
     */
    abstract public static function getGlobalStyles(ItemLibraryGenerator $generator): array;

    /**
     * @param ItemLibraryGenerator $generator
     * @return CSSEntry[]
     */
    public static function getGlobalFallbackStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param ItemLibraryGenerator $generator
     * @return CSSEntry[]
     */
    public static function getGlobalPostStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param ItemLibraryGenerator $generator
     * @return CSSEntry[]
     */
    public static function getGlobalPostFallbackStyles(ItemLibraryGenerator $generator): array
    {
        return [];
    }

    /**
     * @param Item $item
     */
    public function __construct(
        protected Item $item
    )
    {
    }

    /**
     * @return string
     */
    public function getCssSelector(): string
    {
        return static::getItemCssSelector($this->item->getLocator(), $this->item->getGenerator());
    }

    /**
     * @return CSSEntry[]
     */
    abstract public function getItemStyles(): array;

    /**
     * @return CSSEntry[]
     */
    abstract public function getItemFallbackStyles(): array;

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }
}