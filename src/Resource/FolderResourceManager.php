<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\InvalidResourceLocatorException;
use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\ResourceResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\Model;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\AtlasSource\AtlasTextureResolver;
use Aternos\Renderchest\Resource\DynamicResources\ArmorTrimModelGenerator;
use Aternos\Renderchest\Resource\DynamicResources\CrossbowModelGenerator;
use Aternos\Renderchest\Resource\DynamicResources\DecoratedPotModelGenerator;
use Aternos\Renderchest\Resource\DynamicResources\LeatherArmorTrimModelGenerator;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Item\ItemType;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Exception;
use ImagickException;

class FolderResourceManager implements ResourceManagerInterface
{
    protected AtlasTextureResolver $atlasTextureResolver;

    /**
     * @var DynamicResourceGeneratorInterface[]
     */
    protected array $resourceGenerators = [];

    /**
     * @param array $paths
     * @throws Exception
     */
    public function __construct(protected array $paths)
    {
        array_unshift($this->paths, __DIR__ . "/../../builtin/");
        $this->loadTextureSources();
        $this->addDynamicResourceGenerator(new LeatherArmorTrimModelGenerator($this));
        $this->addDynamicResourceGenerator(new DecoratedPotModelGenerator($this));
        $this->addDynamicResourceGenerator(new ArmorTrimModelGenerator($this));
        $this->addDynamicResourceGenerator(new CrossbowModelGenerator($this));
    }

    /**
     * @param DynamicResourceGeneratorInterface $generator
     * @return $this
     */
    public function addDynamicResourceGenerator(DynamicResourceGeneratorInterface $generator): static
    {
        $this->resourceGenerators[$generator::getNamespace()] = $generator;
        return $this;
    }

    /**
     * @param string $namespace
     * @return DynamicResourceGeneratorInterface|null
     */
    protected function getGeneratorFor(string $namespace): ?DynamicResourceGeneratorInterface
    {
        return $this->resourceGenerators[$namespace] ?? null;
    }

    /**
     * @return void
     * @throws InvalidResourceLocatorException
     * @throws ResourceResolutionException
     */
    protected function loadTextureSources(): void
    {
        $this->atlasTextureResolver = new AtlasTextureResolver($this);
        foreach ($this->paths as $path) {
            foreach (scandir($path) as $namespace) {
                if (in_array($namespace, [".", ".."]) || !is_dir($path . "/" . $namespace)) {
                    continue;
                }
                $atlases = $path . "/" . $namespace . "/atlases/";
                if (!file_exists($atlases) || !is_dir($atlases)) {
                    continue;
                }
                foreach (scandir($atlases) as $atlas) {
                    $atlasFile = $atlases . $atlas;
                    if (!is_file($atlasFile)) {
                        continue;
                    }
                    $content = json_decode(file_get_contents($atlasFile));
                    if (!is_object($content) || !is_array($content->sources)) {
                        continue;
                    }
                    foreach ($content->sources as $source) {
                        $this->atlasTextureResolver->add($namespace, $source);
                    }
                }
            }
        }
    }

    /**
     * @param string $file
     * @param string|null $extension
     * @return string|null
     */
    protected function getFile(string $file, ?string $extension = null): ?string
    {
        if ($extension !== null && !str_ends_with($file, "." . $extension)) {
            $file .= "." . $extension;
        }
        foreach ($this->paths as $path) {
            if (file_exists($path . "/" . $file)) {
                return $path . "/" . $file;
            }
        }
        return null;
    }

    /**
     * @inheritDoc
     * @throws ModelResolutionException
     * @throws TextureResolutionException
     * @throws Exception
     */
    function getModel(ResourceLocator $locator, ?Model $model = null): ModelInterface
    {
        if ($generator = $this->getGeneratorFor($locator->getNamespace())) {
            return $generator->getModel($locator);
        }
        $path = $this->getFile($locator->getNamespace() . "/models/" . $locator->getPath(), "json");
        if ($path === null) {
            throw new ModelResolutionException("Cannot resolve model locator " . $locator);
        }

        $data = json_decode(file_get_contents($path));

        if (isset($data->parent)) {
            if (preg_match("#^(minecraft:)?builtin/generated$#", $data->parent)) {
                $model = new GeneratedItem();
            } else {
                $model = $this->getModel(ResourceLocator::parse($data->parent), $model);
            }
        }

        if ($model === null) {
            $model = new Model();
        }

        $model->applyModelData($data, $this);
        return $model;
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     * @throws TextureResolutionException
     */
    function getTexture(ResourceLocator $locator): TextureInterface
    {
        if ($generator = $this->getGeneratorFor($locator->getNamespace())) {
            return $generator->getTexture($locator);
        }
        $texture = $this->atlasTextureResolver->getTexture($locator);
        if ($texture === null) {
            throw new TextureResolutionException("Cannot resolve texture locator " . $locator);
        }
        return $texture;
    }

    /**
     * @inheritDoc
     */
    function getAllItems(string $namespace): array
    {
        $items = [];
        foreach ($this->paths as $path) {
            $folder = $path . "/" . $namespace . "/items/";
            if (!file_exists($folder) || !is_dir($folder)) {
                continue;
            }

            foreach (scandir($folder) as $file) {
                if (!str_ends_with($file, ".json")) {
                    continue;
                }
                $name = $namespace . ":" . substr($file, 0, -5);
                if (!in_array($name, $items, true)) {
                    $items[] = $name;
                }
            }
        }

        foreach ($this->resourceGenerators as $generator) {
            $items = array_merge($items, $generator->getAllItems($namespace));
        }

        return $items;
    }

    /**
     * @inheritDoc
     * @throws FileResolutionException
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string
    {
        if ($generator = $this->getGeneratorFor($locator->getNamespace())) {
            return $generator->getFileContent($locator, $extension);
        }
        $path = $this->getFile($locator->getNamespace() . "/" . $locator->getPath(), $extension);
        if ($path === null) {
            throw new FileResolutionException("Cannot resolve file locator " . $locator);
        }

        return file_get_contents($path);
    }

    /**
     * @inheritDoc
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool
    {
        if ($generator = $this->getGeneratorFor($locator->getNamespace())) {
            return $generator->hasFile($locator, $extension);
        }
        return !!$this->getFile($locator->getNamespace() . "/" . $locator->getPath(), $extension);
    }

    /**
     * @inheritDoc
     */
    function getItem(ResourceLocator $locator): ItemInterface
    {
        if ($generator = $this->getGeneratorFor($locator->getNamespace())) {
            return $generator->getItem($locator);
        }

        $path = $this->getFile($locator->getNamespace() . "/items/" . $locator->getPath(), "json");
        if ($path === null) {
            throw new ItemResolutionException("Cannot resolve model locator " . $locator);
        }

        $data = json_decode(file_get_contents($path));
        return ItemType::createFromData($data->model, $this);
    }
}
