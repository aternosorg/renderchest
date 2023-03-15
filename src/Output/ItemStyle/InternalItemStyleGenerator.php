<?php

namespace Aternos\Renderchest\Output\ItemStyle;

use Aternos\Renderchest\Output\Item;
use Aternos\Renderchest\Output\ItemLibraryGenerator;

class InternalItemStyleGenerator extends ItemStyleGenerator
{

    /**
     * @inheritDoc
     */
    public static function hasItemStyle(Item $item): bool
    {
        return str_starts_with($item->getLocator(), "minecraft:rc_");
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
