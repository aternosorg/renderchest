<?php

namespace Aternos\Renderchest\Model\Face;

use Exception;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use ImagickPixelIterator;
use ImagickPixelIteratorException;
use Iterator;

class FaceImage implements Iterator
{
    protected ImagickPixelIterator $colorIterator;
    protected ImagickPixelIterator $depthIterator;

    /**
     * @var ImagickPixel[]
     */
    protected array $colorPixelRow = [];

    /**
     * @var ImagickPixel[]
     */
    protected array $depthPixelRow = [];

    protected int $rowIndex = 0;

    protected int $width;
    protected int $height;

    protected bool $syncImage = false;

    /**
     * @param Imagick $color
     * @param Imagick $depth
     * @throws ImagickException
     * @throws ImagickPixelIteratorException
     * @throws Exception
     */
    public function __construct(protected Imagick $color, protected Imagick $depth)
    {
        $this->colorIterator = $this->color->getPixelIterator();
        $this->depthIterator = $this->depth->getPixelIterator();

        $this->rowIndex = 0;
        $this->colorPixelRow = $this->colorIterator->current();
        $this->depthPixelRow = $this->depthIterator->current();

        $this->width = $this->color->getImageWidth();
        $this->height = $this->color->getImageHeight();

        if($this->width != $this->depth->getImageWidth() || $this->height != $this->depth->getImageHeight()) {
            throw new Exception("Color and depth image must have the same size");
        }
    }

    /**
     * @inheritDoc
     */
    public function current(): ?ImagickPixel
    {
        return $this->colorPixelRow[$this->rowIndex] ?? null;
    }

    /**
     * @return float
     * @throws ImagickPixelException
     */
    public function currentDepth(): float
    {
        $depth = $this->depthPixelRow[$this->rowIndex] ?? null;
        return $depth?->getColorValue(Imagick::COLOR_RED) ?? 0;
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelIteratorException
     */
    public function next(): void
    {
        $this->rowIndex++;
        if($this->rowIndex >= $this->width) {
            $this->rowIndex = 0;

            if($this->syncImage) {
                $this->colorIterator->syncIterator();
                $this->depthIterator->syncIterator();
            }

            $this->colorIterator->next();
            $this->depthIterator->next();
            $this->colorPixelRow = $this->colorIterator->current();
            $this->depthPixelRow = $this->depthIterator->current();
        }
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelIteratorException
     */
    public function key(): int
    {
        return $this->colorIterator->key() + $this->rowIndex;
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelIteratorException
     */
    public function valid(): bool
    {
        return $this->colorIterator->valid() &&
            $this->depthIterator->valid() &&
            $this->rowIndex < $this->width;
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelIteratorException
     */
    public function rewind(): void
    {
        $this->colorIterator->rewind();
        $this->depthIterator->rewind();
        $this->rowIndex = 0;
        $this->colorPixelRow = $this->colorIterator->current();
        $this->depthPixelRow = $this->depthIterator->current();
    }

    /**
     * @param bool $syncImage
     * @return $this
     */
    public function setSyncImage(bool $syncImage): static
    {
        $this->syncImage = $syncImage;
        return $this;
    }
}
