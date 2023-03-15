<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource;

use Aternos\Renderchest\Resource\ResourceLocator;
use Exception;

class SingleAtlasTextureSource extends AtlasTextureSource
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getTextureLocator(ResourceLocator $locator): ?ResourceLocator
    {
        $sprite = $this->settings->sprite ?? $this->settings->resource;
        if (!$locator->is(ResourceLocator::parse($sprite, $this->namespace))) {
            return null;
        }
        return parent::getTextureLocator(ResourceLocator::parse($this->settings->resource, $this->namespace));
    }
}