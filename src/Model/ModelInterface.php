<?php

namespace Aternos\Renderchest\Model;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Resource\Texture\TextureList;
use Aternos\Renderchest\Tinter\Tinterface;
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
     * @return Imagick
     * @throws ImagickException
     * @throws Exception
     * @throws TextureResolutionException
     */
    public function render(int $width, int $height): Imagick;

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @param Tinterface|null $tinter
     * @return void
     */
    public function applyModelData(stdClass $data, ResourceManagerInterface $resourceManager, ?Tinterface $tinter): void;
}
