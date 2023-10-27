<?php

namespace Aternos\Renderchest\Output;

use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\FolderResourceManager;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Taskmaster\Task\OnBoth;
use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;
use Exception;
use Imagick;
use ImagickException;

class ItemRenderTask extends Task
{
    public function __construct(
        #[OnChild] protected string $itemName,
        #[OnChild] protected int $size,
        #[OnChild] protected int $quality,
        #[OnChild] protected array $assets,
        #[OnChild] protected string $format,
        #[OnChild] protected bool $createPngFallback,
        #[OnChild] protected string $output
    )
    {
    }

    /**
     * @inheritDoc
     * @throws ImagickException
     * @throws TextureResolutionException
     * @throws Exception
     */
    #[OnChild] public function run()
    {
        $resourceManager = new FolderResourceManager($this->assets);
        $locator = ResourceLocator::parse($this->itemName);
        $normalizedLocator = $locator->clone()->setPath(basename($locator->getPath()));
        $key = strval($normalizedLocator);

        try {
            $model = $resourceManager->getModel($locator);
        } catch (ModelResolutionException) {
            return null;
        }

        try {
            $out = $model->render($this->size * $this->quality, $this->size * $this->quality);
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
