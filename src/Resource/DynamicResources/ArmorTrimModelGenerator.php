<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\ResourceLocator;
use Exception;

class ArmorTrimModelGenerator extends DynamicResourceGenerator
{
    protected ?array $models = null;

    /**
     * @inheritDoc
     */
    public static function getNamespace(): string
    {
        return "_armor_trims";
    }

    /**
     * @return void
     */
    protected function initializeModels(): void
    {
        if ($this->models !== null) {
            return;
        }
        $this->models = [];
        foreach (Constants::ARMOR_ITEM_TYPES as $type) {
            foreach (Constants::TRIM_MATERIALS as $trimMaterial) {
                $this->models[] = $type . "_trim_" . $trimMaterial;
                if (in_array($trimMaterial, Constants::ARMOR_MATERIALS)) {
                    $this->models[] = $type . "_trim_" . $trimMaterial . "_darker";
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllItems(string $namespace): array
    {
        $this->initializeModels();
        $items = [];
        foreach ($this->models as $model) {
            $items[] = strval(new ResourceLocator(static::getNamespace(), $model));
        }
        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getModel(ResourceLocator $locator): ModelInterface
    {
        $model = new GeneratedItem();
        try {
            $model->getTextures()->set("layer0", "minecraft:trims/items/" . $locator->getPath(), $this->resourceManager);
        } catch (Exception $e) {
            throw new ModelResolutionException("Cannot resolve model locator " . $locator, 0, $e);
        }
        return $model;
    }
}
