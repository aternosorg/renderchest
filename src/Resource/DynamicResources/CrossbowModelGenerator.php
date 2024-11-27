<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Item\Properties\StringProperty;
use Aternos\Renderchest\Resource\ResourceLocator;

class CrossbowModelGenerator extends DynamicResourceGenerator
{
    /**
     * @inheritDoc
     */
    public static function getNamespace(): string
    {
        return "_crossbow";
    }

    /**
     * @inheritDoc
     */
    public function getItem(ResourceLocator $locator): ItemInterface
    {
        $item = $this->resourceManager->getItem(new ResourceLocator("minecraft", "crossbow"));
        $item->getProperties()->set(new StringProperty("minecraft:charge_type", $locator->getPath()));
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getAllItems(string $namespace): array
    {
        return [
            strval(new ResourceLocator(static::getNamespace(), "arrow")),
            strval(new ResourceLocator(static::getNamespace(), "rocket")),
        ];
    }
}
