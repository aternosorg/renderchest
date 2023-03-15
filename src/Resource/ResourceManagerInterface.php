<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Model\ModelInterface;
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
     * @param string $namespace
     * @return array
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
     */
    function getFileContent(ResourceLocator $locator, ?string $extension = null): string;

    /**
     * @param ResourceLocator $locator
     * @param string|null $extension
     * @return bool
     */
    function hasFile(ResourceLocator $locator, ?string $extension = null): bool;
}
