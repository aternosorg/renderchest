<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\GeneratedItem;
use Aternos\Renderchest\Model\Model;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\AtlasSource\AtlasTextureResolver;
use Aternos\Renderchest\Resource\DynamicResources\DecoratedPotModelGenerator;
use Aternos\Renderchest\Resource\DynamicResources\LeatherArmorTrimModelGenerator;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Tinter\TinterManager;
use Exception;
use ImagickException;

class FolderResourceManager implements ResourceManagerInterface
{
    protected TinterManager $tinters;
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
        $this->tinters = new TinterManager($this);
        array_unshift($this->paths, __DIR__ . "/../../builtin/");
        $this->loadTextureSources();
        $this->addDynamicResourceGenerator(new LeatherArmorTrimModelGenerator($this));
        $this->addDynamicResourceGenerator(new DecoratedPotModelGenerator($this));
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

        $tinter = $this->tinters->getTinter($locator);

        $model->applyModelData($data, $this, $tinter);
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
            $folder = $path . "/" . $namespace . "/models/item/";
            if (!file_exists($folder) || !is_dir($folder)) {
                continue;
            }

            foreach (scandir($folder) as $file) {
                if (!str_ends_with($file, ".json")) {
                    continue;
                }
                $name = $namespace . ":" . "item/" . substr($file, 0, -5);
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
}
