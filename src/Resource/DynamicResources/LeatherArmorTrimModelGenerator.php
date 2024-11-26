<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\ResourceLocator;
use Exception;

class LeatherArmorTrimModelGenerator extends DynamicResourceGenerator
{
    protected ?array $models = null;

    /**
     * @inheritDoc
     */
    public static function getNamespace(): string
    {
        return "_leather_armor";
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initializeModels(): void
    {
        if ($this->models !== null) {
            return;
        }
        $this->models = [];
        foreach (Constants::ARMOR_ITEM_TYPES as $armorItem) {
            $this->createModelFromLayers("leather_" . $armorItem . "_base", [
                "minecraft:item/leather_" . $armorItem
            ]);

            $this->createModelFromLayers("leather_" . $armorItem . "_overlay", [
                "minecraft:item/leather_" . $armorItem . "_overlay"
            ]);

            foreach (Constants::TRIM_MATERIALS as $trimMaterial) {
                $this->createModelFromLayers("leather_" . $armorItem . "_" . $trimMaterial . "_trim", [
                    "minecraft:item/leather_" . $armorItem . "_overlay",
                    "minecraft:trims/items/" . $armorItem . "_trim_" . $trimMaterial
                ]);
            }
        }
    }

    /**
     * @param string $locatorPath
     * @param string[] $layers
     * @return GeneratedItem
     * @throws Exception
     */
    protected function createModelFromLayers(string $locatorPath, array $layers): GeneratedItem
    {
        $model = new GeneratedItem();
        foreach ($layers as $i => $layer) {
            $model->getTextures()->set("layer" . $i, $layer, $this->resourceManager);
        }

        $itemId = new ResourceLocator(static::getNamespace(), $locatorPath);
        $this->models[strval($itemId)] = $model;

        return $model;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getModel(ResourceLocator $locator): ModelInterface
    {
        $this->initializeModels();
        if (!isset($this->models[strval($locator)])) {
            throw new ModelResolutionException("Cannot resolve model locator " . $locator);
        }
        return $this->models[strval($locator)];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getAllItems(string $namespace): array
    {
        $this->initializeModels();
        return array_keys($this->models);
    }

    /**
     * @inheritDoc
     */
    function getItem(ResourceLocator $locator): ItemInterface
    {
        // TODO: Implement getItem() method.
    }
}
