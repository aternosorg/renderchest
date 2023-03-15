<?php

namespace Aternos\Renderchest\Resource\AtlasSource;

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
        "directory" => DirectoryAtlasTextureSource::class,
        "single" => SingleAtlasTextureSource::class,
        "unstitch" => UnstitchAtlasTextureSource::class,
        "paletted_permutations" => PalettedPermutationsTextureSource::class
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
     */
    public function add(string $namespace, stdClass $settings): static
    {
        $class = static::SOURCES[$settings->type] ?? null;
        if ($class === null) {
            return $this;
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

