<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;

class CombinedResourceManager implements ResourceManagerInterface
{
    /**
     * @inheritDoc
     */
    public static function fromSerialized(mixed $data): static
    {
        $resourceManagers = [];
        foreach ($data as $resourceManagerData) {
            $class = $resourceManagerData["class"];
            $resourceManagers[] = $class::fromSerialized($resourceManagerData["data"]);
        }
        return new static($resourceManagers);
    }

    /**
     * @param ResourceManagerInterface[] $resourceManagers
     */
    public function __construct(
        protected array $resourceManagers = []
    )
    {
    }

    /**
     * @inheritDoc
     */
    function getModel(ResourceLocator $locator): ModelInterface
    {
        $lastError = null;
        foreach ($this->resourceManagers as $resourceManager) {
            try {
                return $resourceManager->getModel($locator);
            } catch (ModelResolutionException $e) {
                $lastError = $e;
            }
        }
        throw $lastError ?? new ModelResolutionException("Model " . $locator . " could not be resolved");
    }

    /**
     * @inheritDoc
     */
    function getItem(ResourceLocator $locator): ItemInterface
    {
        $lastError = null;
        foreach ($this->resourceManagers as $resourceManager) {
            try {
                return $resourceManager->getItem($locator);
            } catch (ItemResolutionException $e) {
                $lastError = $e;
            }
        }
        throw $lastError ?? new ItemResolutionException("Item " . $locator . " could not be resolved");
    }

    /**
     * @inheritDoc
     */
    function getAllItems(string $namespace): array
    {
        $results = [];
        foreach ($this->resourceManagers as $resourceManager) {
            $results = array_merge($results, $resourceManager->getAllItems($namespace));
        }
        return array_values(array_unique($results));
    }

    /**
     * @inheritDoc
     */
    function getTexture(ResourceLocator $locator): TextureInterface
    {
        $lastError = null;
        foreach ($this->resourceManagers as $resourceManager) {
            try {
                return $resourceManager->getTexture($locator);
            } catch (TextureResolutionException $e) {
                $lastError = $e;
            }
        }
        throw $lastError ?? new TextureResolutionException("Texture " . $locator . " could not be resolved");
    }

    /**
     * @inheritDoc
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string
    {
        $lastError = null;
        foreach ($this->resourceManagers as $resourceManager) {
            try {
                return $resourceManager->getFileContent($locator, $extension);
            } catch (FileResolutionException $e) {
                $lastError = $e;
            }
        }
        throw $lastError ?? new FileResolutionException("File " . $locator . " could not be resolved");
    }

    /**
     * @inheritDoc
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool
    {
        foreach ($this->resourceManagers as $resourceManager) {
            if ($resourceManager->hasFile($locator, $extension)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    function serialize(): array
    {
        $result = [];
        foreach ($this->resourceManagers as $resourceManager) {
            $result[] = [
                "class" => $resourceManager::class,
                "data" => $resourceManager->serialize()
            ];
        }
        return $result;
    }
}
