<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource;

use Aternos\Renderchest\Resource\ResourceLocator;

class DirectoryAtlasTextureSource extends AtlasTextureSource
{
    /**
     * @inheritDoc
     */
    protected function getTextureLocator(ResourceLocator $locator): ?ResourceLocator
    {
        $path = $locator->getPath();
        if (!str_starts_with($path, $this->settings->prefix)) {
            return null;
        }
        $source = str_ends_with($this->settings->source, "/") ? $this->settings->source : $this->settings->source . "/";
        return parent::getTextureLocator($locator->clone()->setPath($source . substr($path, strlen($this->settings->prefix))));
    }
}
