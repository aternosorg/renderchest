<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\DynamicResourceGeneratorInterface;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;

abstract class DynamicResourceGenerator implements DynamicResourceGeneratorInterface
{
    /**
     * @param ResourceManagerInterface $resourceManager
     */
    public function __construct(
        protected ResourceManagerInterface $resourceManager
    )
    {
    }

    /**
     * @inheritDoc
     */
    function getModel(ResourceLocator $locator): ModelInterface
    {
        throw new ModelResolutionException("Cannot resolve model locator " . $locator);
    }

    /**
     * @inheritDoc
     */
    function getAllItems(string $namespace): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    function getTexture(ResourceLocator $locator): TextureInterface
    {
        throw new TextureResolutionException("Cannot resolve texture locator " . $locator);
    }

    /**
     * @inheritDoc
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string
    {
        throw new FileResolutionException("Cannot resolve file locator " . $locator);
    }

    /**
     * @inheritDoc
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool
    {
        return false;
    }
}
