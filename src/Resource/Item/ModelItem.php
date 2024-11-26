<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Exception\InvalidResourceLocatorException;
use Aternos\Renderchest\Exception\InvalidTinterDefinitionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Tinter\TinterList;
use Aternos\Renderchest\Tinter\TintSourceType;
use Exception;
use Imagick;
use stdClass;

class ModelItem implements ItemInterface
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        if (!isset($data->model) || !is_string($data->model)) {
            throw new InvalidItemDefinitionException("Model item requires a model resource locator");
        }

        try {
            $modelLocator = ResourceLocator::parse($data->model);
        } catch (InvalidResourceLocatorException $e) {
            throw new InvalidItemDefinitionException("Invalid model resource locator: " . $e->getMessage(), 0, $e);
        }

        $tints = [];
        foreach ($data->tints ?? [] as $tint) {
            if (!($tint instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Tint must be an object");
            }
            try {
                $tints[] = TintSourceType::createFromData($tint, $resourceManager);
            } catch (InvalidTinterDefinitionException $e) {
                throw new InvalidItemDefinitionException("Invalid tint definition: " . $e->getMessage(), 0, $e);
            }
        }

        try {
            $model = $resourceManager->getModel($modelLocator);
        } catch (ModelResolutionException $e) {
            throw new InvalidItemDefinitionException("Model resolution failed: " . $e->getMessage(), 0, $e);
        }

        return new static($model, new TinterList($tints));
    }

    /**
     * @param ResourceManagerInterface $resourceManager
     * @return static
     * @throws InvalidItemDefinitionException
     */
    public static function createUnknown(ResourceManagerInterface $resourceManager): static
    {
        try {
            return new ModelItem($resourceManager->getModel(
                new ResourceLocator("renderchest", "item/unknown")), new TinterList()
            );
        } catch (Exception $e) {
            throw new InvalidItemDefinitionException("Failed to create unknown item fallback model", 0, $e);
        }
    }

    /**
     * @param ModelInterface $model
     * @param TinterList $tints
     */
    public function __construct(
        protected ModelInterface $model,
        protected TinterList     $tints
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        return $this->model->render($width, $height, $this->tints);
    }
}
