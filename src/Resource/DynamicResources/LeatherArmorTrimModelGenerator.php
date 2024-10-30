<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Exception;

class LeatherArmorTrimModelGenerator extends DynamicResourceGenerator
{
    protected array $models = [];

    /**
     * @inheritDoc
     */
    public static function getNamespace(): string
    {
        return "_leather_armor";
    }

    /**
     * @param ResourceManagerInterface $resourceManager
     * @throws Exception
     */
    public function __construct(ResourceManagerInterface $resourceManager)
    {
        parent::__construct($resourceManager);
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
     */
    public function getModel(ResourceLocator $locator): ModelInterface
    {
        if (!isset($this->models[strval($locator)])) {
            throw new ModelResolutionException("Cannot resolve model locator " . $locator);
        }
        return $this->models[strval($locator)];
    }

    /**
     * @inheritDoc
     */
    public function getAllItems(string $namespace): array
    {
        return array_keys($this->models);
    }
}
