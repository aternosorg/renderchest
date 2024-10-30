<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Constants;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\ResourceLocator;

class DecoratedPotModelGenerator extends DynamicResourceGenerator
{
    protected ?array $models = null;

    /**
     * @inheritDoc
     */
    public static function getNamespace(): string
    {
        return "_decorated_pot";
    }

    /**
     * @return void
     * @throws ModelResolutionException
     */
    protected function initializeModels(): void
    {
        if ($this->models !== null) {
            return;
        }
        $this->models = [];
        foreach (Constants::POTTERY_SHERDS as $sherd) {
            $this->createBasePot($sherd);
            $this->createOverlayModel($sherd);
        }
    }

    /**
     * @param string $sherd
     * @return ModelInterface
     * @throws ModelResolutionException
     */
    protected function createBasePot(string $sherd): ModelInterface
    {
        $base = $this->resourceManager->getModel(new ResourceLocator("renderchest", "item/decorated_pot"));
        $base->getTextures()->set("north", "#brick", $this->resourceManager);
        $base->getTextures()->set("south", "#brick", $this->resourceManager);
        $base->getTextures()->set("west", "#" . $sherd, $this->resourceManager);
        $base->getTextures()->set("east", "#brick", $this->resourceManager);

        $itemId = new ResourceLocator(static::getNamespace(), "pot_b_" . $sherd);
        $this->models[strval($itemId)] = $base;
        return $base;
    }

    /**
     * @param string $sherd
     * @return ModelInterface
     * @throws ModelResolutionException
     */
    protected function createOverlayModel(string $sherd): ModelInterface
    {
        $overlay = $this->resourceManager->getModel(new ResourceLocator("renderchest", "item/decorated_pot_overlay"));
        $overlay->getTextures()->set("south", "#" . $sherd, $this->resourceManager);

        $itemId = new ResourceLocator(static::getNamespace(), "pot_o_" . $sherd);
        $this->models[strval($itemId)] = $overlay;
        return $overlay;
    }

    /**
     * @inheritDoc
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
     * @throws ModelResolutionException
     */
    public function getAllItems(string $namespace): array
    {
        $this->initializeModels();
        return array_keys($this->models);
    }
}
