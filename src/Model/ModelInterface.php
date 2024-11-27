<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\TinterList;
use Exception;
use Imagick;
use ImagickException;
use stdClass;

interface ModelInterface
{
    /**
     * @return ModelDisplaySettings
     */
    public function getDisplaySettings(): ModelDisplaySettings;

    /**
     * @return TextureList
     */
    public function getTextures(): TextureList;

    /**
     * @return bool
     * @throws TextureResolutionException
     */
    public function hasAnimatedTextures(): bool;

    /**
     * @return ModelGuiLight
     */
    public function getGuiLight(): ModelGuiLight;

    /**
     * @param int $width
     * @param int $height
     * @param TinterList|null $tinters
     * @return Imagick
     * @throws ImagickException
     * @throws Exception
     * @throws TextureResolutionException
     */
    public function render(int $width, int $height, ?TinterList $tinters = null): Imagick;

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return void
     */
    public function applyModelData(stdClass $data, ResourceManagerInterface $resourceManager): void;
}
