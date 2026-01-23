<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Item\ModelItem;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\Texture\FileTexture;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureMeta;
use Aternos\Renderchest\Tinter\TinterList;
use ImagickException;

class PreRenderedResourceManager implements ResourceManagerInterface
{
    /**
     * @inheritDoc
     */
    public static function fromSerialized(mixed $data): static
    {
        return new static(
            $data["namespace"],
            $data["basePath"],
            $data["imageType"]
        );
    }

    /**
     * @param string $namespace
     * @param string $basePath
     * @param string $imageType
     */
    public function __construct(
        protected string $namespace,
        protected string $basePath,
        protected string $imageType = "png"
    )
    {
    }

    /**
     * @param ResourceLocator $locator
     * @return string
     */
    protected function getImagePath(ResourceLocator $locator): string
    {
        return $this->basePath . "/" . $locator->getPath() . "." . $this->imageType;
    }

    /**
     * @inheritDoc
     */
    function getModel(ResourceLocator $locator): ModelInterface
    {
        if ($locator->getNamespace() !== $this->namespace) {
            throw new ModelResolutionException("Model namespace '" . $locator->getNamespace() . "' not found in PreRenderedResourceManager");
        }
        $path = $this->getImagePath($locator);
        if (!file_exists($path)) {
            throw new ModelResolutionException("Model '" . $locator->getPath() . "' not found in PreRenderedResourceManager");
        }
        $model = new GeneratedItem();
        $model->getTextures()->set("layer0", $locator, $this);
        return $model;
    }

    /**
     * @inheritDoc
     */
    function getItem(ResourceLocator $locator): ItemInterface
    {
        if ($locator->getNamespace() !== $this->namespace) {
            throw new ItemResolutionException("Item namespace '" . $locator->getNamespace() . "' not found in PreRenderedResourceManager");
        }
        $path = $this->getImagePath($locator);
        if (!file_exists($path)) {
            throw new ItemResolutionException("Item '" . $locator->getPath() . "' not found in PreRenderedResourceManager");
        }
        try {
            return new ModelItem(new Properties(), $this->getModel($locator), new TinterList());
        } catch (ModelResolutionException $e) {
            throw new InvalidItemDefinitionException("Failed to get model for item " . $locator, previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    function getAllItems(string $namespace): array
    {
        if ($namespace !== $this->namespace) {
            return [];
        }
        $items = [];
        foreach (scandir($this->basePath) as $file) {
            if (in_array($file, [".", ".."])) {
                continue;
            }
            if (!str_ends_with($file, "." . $this->imageType)) {
                continue;
            }
            $itemName = substr($file, 0, -strlen("." . $this->imageType));
            $items[] = $namespace . ":" . $itemName;
        }
        return $items;
    }

    /**
     * @inheritDoc
     */
    function getTexture(ResourceLocator $locator): TextureInterface
    {
        if ($locator->getNamespace() !== $this->namespace) {
            throw new TextureResolutionException("Texture namespace '" . $locator->getNamespace() . "' not found in PreRenderedResourceManager");
        }
        $path = $this->getImagePath($locator);
        if (!file_exists($path)) {
            throw new TextureResolutionException("Texture '" . $locator->getPath() . "' not found in PreRenderedResourceManager");
        }
        try {
            return new FileTexture($path, new TextureMeta());
        } catch (ImagickException $e) {
            throw new TextureResolutionException("Failed to load texture '" . $locator . "'", previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string
    {
        throw new FileResolutionException("Not implemented");
    }

    /**
     * @inheritDoc
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    function serialize(): mixed
    {
        return [
            "namespace" => $this->namespace,
            "basePath" => $this->basePath,
            "imageType" => $this->imageType,
        ];
    }
}
