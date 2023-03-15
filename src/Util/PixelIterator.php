<?php

namespace Aternos\Renderchest\Util;

use Exception;
use Imagick;
use ImagickException;
use ImagickPixel;
use ImagickPixelIterator;
use ImagickPixelIteratorException;
use Iterator;

class PixelIterator implements Iterator
{
    protected ImagickPixelIterator $colorIterator;

    /**
     * @var ImagickPixel[]
     */
    protected array $colorPixelRow = [];

    protected int $rowIndex = 0;

    protected int $width;
    protected int $height;

    protected bool $syncImage = false;

    /**
     * @param Imagick $color
     * @throws ImagickException
     * @throws ImagickPixelIteratorException
     * @throws Exception
     */
    public function __construct(protected Imagick $color)
    {
        $this->colorIterator = $this->color->getPixelIterator();

        $this->rowIndex = 0;
        $this->colorPixelRow = $this->colorIterator->current();

        $this->width = $this->color->getImageWidth();
        $this->height = $this->color->getImageHeight();
    }

    /**
     * @inheritDoc
     */
    public function current(): ?ImagickPixel
    {
        return $this->colorPixelRow[$this->rowIndex] ?? null;
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
            }

            $this->colorIterator->next();
            $this->colorPixelRow = $this->colorIterator->current();
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
            $this->rowIndex < $this->width;
    }

    /**
     * @inheritDoc
     * @throws ImagickPixelIteratorException
     */
    public function rewind(): void
    {
        $this->colorIterator->rewind();
        $this->rowIndex = 0;
        $this->colorPixelRow = $this->colorIterator->current();
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
