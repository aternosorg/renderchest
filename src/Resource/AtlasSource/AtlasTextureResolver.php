<?php

namespace Aternos\Renderchest\Resource\AtlasSource;

use Aternos\Renderchest\Exception\InvalidResourceLocatorException;
use Aternos\Renderchest\Exception\ResourceResolutionException;
use Aternos\Renderchest\Resource\AtlasSource\TextureSource\AtlasTextureSource;
use Aternos\Renderchest\Resource\AtlasSource\TextureSource\DirectoryAtlasTextureSource;
use Aternos\Renderchest\Resource\AtlasSource\TextureSource\PalettedPermutationsTextureSource;
use Aternos\Renderchest\Resource\AtlasSource\TextureSource\SingleAtlasTextureSource;
use Aternos\Renderchest\Resource\AtlasSource\TextureSource\UnstitchAtlasTextureSource;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use ImagickException;
use stdClass;

class AtlasTextureResolver
{
    const SOURCES = [
        "minecraft:directory" => DirectoryAtlasTextureSource::class,
        "minecraft:single" => SingleAtlasTextureSource::class,
        "minecraft:unstitch" => UnstitchAtlasTextureSource::class,
        "minecraft:paletted_permutations" => PalettedPermutationsTextureSource::class
    ];

    /**
     * @var AtlasTextureSource[]
     */
    protected array $sources = [];

    public function __construct(protected ResourceManagerInterface $resourceManager)
    {
    }

    /**
     * @param string $namespace
     * @param stdClass $settings
     * @return $this
     * @throws InvalidResourceLocatorException|ResourceResolutionException
     */
    public function add(string $namespace, stdClass $settings): static
    {
        if (!isset($settings->type) || !is_string($settings->type)) {
            throw new ResourceResolutionException("Missing atlas texture source type");
        }
        $type = ResourceLocator::parse($settings->type);
        $class = static::SOURCES[(string) $type] ?? null;
        if ($class === null) {
            throw new ResourceResolutionException("Unknown atlas texture source type " . $type);
        }
        array_unshift($this->sources, new $class($this->resourceManager, $namespace, $settings));
        return $this;
    }

    /**
     * @param ResourceLocator $locator
     * @return AtlasTextureSource|null
     */
    public function getSource(ResourceLocator $locator): ?AtlasTextureSource
    {
        foreach ($this->sources as $source) {
            if ($source->has($locator)) {
                return $source;
            }
        }
        return null;
    }

    /**
     * @param ResourceLocator $locator
     * @return TextureInterface|null
     * @throws ImagickException
     */
    public function getTexture(ResourceLocator $locator): ?TextureInterface
    {
        return $this->getSource($locator)?->getTexture($locator);
    }
}

