<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use ImagickException;
use ImagickPixel;
use stdClass;

class CompositeItem extends AbstractItem
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static
    {
        if (!isset($data->models) || !is_array($data->models)) {
            throw new InvalidItemDefinitionException("Composite item must have a models array");
        }

        $items = [];
        foreach ($data->models as $item) {
            if (!($item instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Item must be an object");
            }

            $items[] = ItemType::createFromData($item, $resourceManager, $properties);
        }

        return new static($properties, $items);
    }

    /**
     * @param ItemInterface[] $items
     */
    public function __construct(
        Properties $properties,
        protected array $items
    )
    {
        parent::__construct($properties);
    }

    /**
     * Return a new Image with the items layered on top of each other
     *
     * @param int $width
     * @param int $height
     * @param array $images
     * @return Imagick
     * @throws ImagickException
     */
    protected function composite(int $width, int $height, array $images): Imagick
    {
        $composite = new Imagick();
        $composite->newImage($width, $height, new ImagickPixel('transparent'));

        foreach ($images as $image) {
            $composite->compositeImage($image, Imagick::COMPOSITE_DEFAULT, 0, 0);
        }

        return $composite;
    }

    /**
     * @param Imagick $image
     * @return int
     * @throws ImagickException
     */
    protected function getAnimationTime(Imagick $image): int
    {
        $animationTime = 0;
        foreach ($image as $frame) {
            $animationTime += $frame->getImageDelay();
        }
        return $animationTime;
    }

    /**
     * @param Imagick $image
     * @param int $time
     * @return void
     * @throws ImagickException
     */
    protected function setCurrentAnimationTime(Imagick $image, int $time): void
    {
        $current = 0;
        $image->setFirstIterator();
        while ($current < $time) {
            $current += $image->getImageDelay();
            if (!$image->nextImage()) {
                $image->setFirstIterator();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        $renders = [];
        foreach ($this->items as $item) {
            $renders[] = $item->render($width, $height);
        }

        $animationTimes = [];
        foreach ($renders as $render) {
            $animationTimes[] = $this->getAnimationTime($render);
        }
        $maxAnimationTime = max($animationTimes);

        $result = new Imagick();
        $currentTime = 0;
        do {
            $nextFrameDelays = [];
            foreach ($renders as $render) {
                $this->setCurrentAnimationTime($render, $currentTime);
                $nextFrameDelays[] = $render->getImageDelay();
            }
            $delay = min($nextFrameDelays);
            $result->addImage($this->composite($width, $height, $renders));
            $result->setImageDelay($delay);
            $currentTime += $delay;
        } while ($currentTime < $maxAnimationTime);

        return $result;
    }
}
