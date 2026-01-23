<?php

namespace Aternos\Renderchest\Output;

use Aternos\Renderchest\Exception\ItemResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Taskmaster\Task\OnBoth;
use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;
use Exception;
use Imagick;
use ImagickException;

class ItemRenderTask extends Task
{
    /**
     * @param string $itemName
     * @param int $size
     * @param int $quality
     * @param class-string<ResourceManagerInterface> $resourceManagerClass
     * @param mixed $serializedResourceManager
     * @param string $format
     * @param bool $createPngFallback
     * @param string $output
     */
    public function __construct(
        #[OnChild] protected string $itemName,
        #[OnChild] protected int $size,
        #[OnChild] protected int $quality,
        #[OnChild] protected string $resourceManagerClass,
        #[OnChild] protected mixed $serializedResourceManager,
        #[OnChild] protected string $format,
        #[OnChild] protected bool $createPngFallback,
        #[OnChild] protected string $output
    )
    {
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     * @throws Exception
     */
    #[OnChild] public function run()
    {
        $resourceManager = $this->resourceManagerClass::fromSerialized($this->serializedResourceManager);
        $locator = ResourceLocator::parse($this->itemName);
        $key = strval($locator);

        try {
            $item = $resourceManager->getItem($locator);
        } catch (ItemResolutionException) {
            return null;
        }

        try {
            $out = $item->render($this->size * $this->quality, $this->size * $this->quality);
        } catch (TextureResolutionException) {
            return null;
        }

        if ($this->quality !== 1) {
            foreach ($out as $frame) {
                $frame->resizeImage($this->size, $this->size, Imagick::FILTER_BOX, 1);
            }
        }

        Item::writeImageFile($out, $key, $this->format, $this->output, $this->createPngFallback);
        return $key;
    }

    /**
     * @return string
     */
    #[OnBoth] public function getItemName(): string
    {
        return $this->itemName;
    }
}
