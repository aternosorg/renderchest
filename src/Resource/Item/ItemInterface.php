<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Exception;
use Imagick;
use ImagickException;
use stdClass;

interface ItemInterface
{
    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @param Properties $properties
     * @return static
     * @throws InvalidItemDefinitionException
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static;

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
     * @return Properties
     */
    public function getProperties(): Properties;
}
