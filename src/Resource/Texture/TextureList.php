<?php

namespace Aternos\Renderchest\Resource\Texture;

use Aternos\Renderchest\Exception\InvalidResourceLocatorException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Exception;

class TextureList
{
    /**
     * @var TextureInterface[]
     */
    protected array $textures = [];

    /**
     * @param string $name
     * @param string $ref
     * @param ResourceManagerInterface $resourceManager
     * @return $this
     * @throws TextureResolutionException
     * @throws InvalidResourceLocatorException
     */
    public function set(string $name, string $ref, ResourceManagerInterface $resourceManager): static
    {
        if (str_starts_with($ref, "#")) {
            $texture = new ResolvableTexture($this, substr($ref, 1));
        } else {
            $texture = $resourceManager->getTexture(ResourceLocator::parse($ref));
        }
        $this->textures[$name] = $texture;
        return $this;
    }

    /**
     * @param string $name
     * @return TextureInterface
     * @throws TextureResolutionException
     */
    public function get(string $name): TextureInterface
    {
        if (str_starts_with($name, "#")) {
            $name = substr($name, 1);
        }
        if (!isset($this->textures[$name])) {
            throw new TextureResolutionException("Unknown texture '" . $name . "'");
        }
        return $this->textures[$name];
    }

    /**
     * @param string $name
     * @return TextureInterface
     * @throws Exception
     */
    public function getResolvable(string $name): TextureInterface
    {
        if (str_starts_with($name, "#")) {
            $name = substr($name, 1);
        }
        return new ResolvableTexture($this, $name);
    }

    /**
     * @return TextureInterface[]
     */
    public function getAll(): array
    {
        return $this->textures;
    }
}
