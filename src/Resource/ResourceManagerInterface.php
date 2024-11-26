<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\FileResolutionException;
use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
use Aternos\Renderchest\Resource\Item\ItemInterface;
use Aternos\Renderchest\Resource\Texture\TextureInterface;

interface ResourceManagerInterface
{
    /**
     * @param ResourceLocator $locator
     * @return ModelInterface
     * @throws ModelResolutionException
     */
    function getModel(ResourceLocator $locator): ModelInterface;

    /**
     * @param ResourceLocator $locator
     * @return ItemInterface
     * @throws ItemResolutionException
     * @throws InvalidItemDefinitionException
     */
    function getItem(ResourceLocator $locator): ItemInterface;

    /**
     * @param string $namespace
     * @return string[]
     */
    function getAllItems(string $namespace): array;

    /**
     * @param ResourceLocator $locator
     * @return TextureInterface
     * @throws TextureResolutionException
     */
    function getTexture(ResourceLocator $locator): TextureInterface;

    /**
     * @param ResourceLocator $locator
     * @param string|null $extension
     * @return string
     * @throws FileResolutionException
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string;

    /**
     * @param ResourceLocator $locator
     * @param string|null $extension
     * @return bool
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool;
}
