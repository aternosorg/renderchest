<?php

namespace Aternos\Renderchest\Output;

use Aternos\Renderchest\Exception\ModelResolutionException;
use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Resource\FolderResourceManager;
use Aternos\Renderchest\Resource\ResourceLocator;
use Closure;
use Exception;
use Imagick;
use Spatie\Async\Pool;

class AsyncItemLibraryGenerator extends ItemLibraryGenerator
{
    protected int $batchSize = 16;
    protected int $maxProcesses = 32;

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize(int $batchSize): static
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    /**
     * @param int $maxProcesses
     * @return $this
     */
    public function setMaxProcesses(int $maxProcesses): static
    {
        $this->maxProcesses = $maxProcesses;
        return $this;
    }

    /**
     * @param string[] $items
     * @return string[][]
     * @throws Exception
     */
    protected function makeBatches(array $items): array
    {
        $regular = [];
        $animated = [];

        foreach ($items as $item) {
            try {
                $model = $this->resourceManager->getModel(ResourceLocator::parse($item));
            } catch (ModelResolutionException) {
                $regular[] = $item;
                continue;
            }

            try {
                $isAnimated = $model->hasAnimatedTextures();
            } catch (TextureResolutionException) {
                $regular[] = $item;
                continue;
            }

            if ($isAnimated) {
                $animated[] = $item;
            } else {
                $regular[] = $item;
            }
        }

        $batches = array_chunk($regular, $this->batchSize);

        while (count($animated) > 0) {
            foreach ($batches as &$batch) {
                if(count($animated) === 0) {
                    break;
                }
                $batch[] = array_shift($animated);
            }
        }

        return $batches;
    }

    /**
     * @inheritDoc
     */
    protected function createItems(int $size, int $quality = 1, callable|Closure|null $onProgress = null): array
    {
        $items = $this->getItemNames();
        $self = $this;
        $assets = $this->assets;
        $format = $this->getFormat();
        $createPngFallback = $this->createPngFallback;
        $output = $this->output;

        $batches = $this->makeBatches($items);
        $total = count($items);
        $progress = 0;
        $results = [];

        $pool = Pool::create();
        $pool->concurrency($this->maxProcesses);
        $pool->timeout(600);

        foreach ($batches as $batch) {
            $pool->add(function () use ($batch, $size, $quality, $assets, $format, $createPngFallback, $output) {
                $resourceManager = new FolderResourceManager($assets);
                $results = [];
                foreach ($batch as $i => $name) {
                    $locator = ResourceLocator::parse($name);
                    $normalizedLocator = $locator->clone()->setPath(basename($locator->getPath()));
                    $key = strval($normalizedLocator);
                    $results[$key] = false;

                    try {
                        $model = $resourceManager->getModel($locator);
                    } catch (ModelResolutionException) {
                        continue;
                    }

                    try {
                        $out = $model->render($size * $quality, $size * $quality);
                    } catch (TextureResolutionException) {
                        continue;
                    }

                    if ($quality !== 1) {
                        foreach ($out as $frame) {
                            $frame->resizeImage($size, $size, Imagick::FILTER_BOX, 1);
                        }
                    }

                    Item::writeImageFile($out, $key, $format, $output, $createPngFallback);
                    $results[$key] = true;
                }
                return $results;
            })->then(function ($res) use (&$results, &$progress, $total, $onProgress, $self) {
                foreach ($res as $name => $item) {
                    $progress++;
                    if ($onProgress !== null) {
                        $onProgress($progress, $total, $name);
                    }

                    if (!$item) {
                        continue;
                    }
                    $results[$name] = new Item($name, $self);
                }
            });
        }
        $pool->wait();
        return $results;
    }
}
