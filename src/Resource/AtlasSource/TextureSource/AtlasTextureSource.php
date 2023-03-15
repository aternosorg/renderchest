<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource;

use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\BufferTexture;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Resource\Texture\TextureMeta;
use Exception;
use ImagickException;
use stdClass;

abstract class AtlasTextureSource
{
    public function __construct(protected ResourceManagerInterface $resourceManager, protected string $namespace, protected stdClass $settings)
    {
    }

    /**
     * @param ResourceLocator $locator
     * @return ?ResourceLocator
     */
    protected function getTextureLocator(ResourceLocator $locator): ?ResourceLocator
    {
        if ($locator->getNamespace() !== $this->namespace) {
            return null;
        }
        return $locator->clone()->setPath("textures/" . $locator->getPath() . (str_ends_with($locator->getPath(), ".png") ? "" : ".png"));
    }

    /**
     * @param ResourceLocator $locator
     * @return bool
     */
    public function has(ResourceLocator $locator): bool
    {
        $locator = $this->getTextureLocator($locator);
        if (!$locator) {
            return false;
        }
        return $this->resourceManager->hasFile($locator, "png");
    }

    /**
     * @param ResourceLocator $locator
     * @return TextureInterface
     * @throws ImagickException
     * @throws Exception
     */
    public function getTexture(ResourceLocator $locator): TextureInterface
    {
        $fileLocator = $this->getTextureLocator($locator);
        if ($fileLocator === null) {
            throw new Exception("Texture " . $locator . " not found");
        }
        $imageData = $this->resourceManager->getFileContent($fileLocator);

        $metaLocator = $fileLocator->clone()->setPath($fileLocator->getPath() . ".mcmeta");
        if ($this->resourceManager->hasFile($metaLocator)) {
            $meta = json_decode($this->resourceManager->getFileContent($metaLocator));
        } else {
            $meta = new stdClass();
        }

        return new BufferTexture($imageData, TextureMeta::fromTextureData($meta));
    }
}
