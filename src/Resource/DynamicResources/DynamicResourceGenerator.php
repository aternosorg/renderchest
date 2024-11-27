<?php

namespace Aternos\Renderchest\Resource\DynamicResources;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\DynamicResourceGeneratorInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Item\ModelItem;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;
use Aternos\Renderchest\Tinter\TinterList;

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

    /**
     * @inheritDoc
     */
    function getItem(ResourceLocator $locator): ItemInterface
    {
        try {
            $model = $this->getModel($locator);
        } catch (ModelResolutionException $e) {
            throw new ItemResolutionException("Cannot resolve item locator " . $locator, 0, $e);
        }

        return new ModelItem(new Properties(), $model, new TinterList());
    }
}
