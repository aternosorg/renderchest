<?php

namespace Aternos\Renderchest\Resource\AtlasSource\TextureSource\Permutations;

use Aternos\Renderchest\Resource\ResourceLocator;

class PermutatedTextureInfo
{
    protected ResourceLocator $locator;

    public function __construct(protected ResourceLocator $baseTextureLocator, protected ResourceLocator $permutationLocator, protected string $suffix)
    {
        $path = $this->baseTextureLocator->getPath();
        if (str_ends_with($path, ".png")) {
            $path = substr($path, 0, -4);
        }
        $this->locator = $this->baseTextureLocator->clone()->setPath($path . "_" . $this->suffix);
    }

    /**
     * @return ResourceLocator
     */
    public function getBaseTextureLocator(): ResourceLocator
    {
        return $this->baseTextureLocator;
    }

    /**
     * @return ResourceLocator
     */
    public function getPermutationLocator(): ResourceLocator
    {
        return $this->permutationLocator;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @return ResourceLocator
     */
    public function getLocator(): ResourceLocator
    {
        return $this->locator;
    }
}