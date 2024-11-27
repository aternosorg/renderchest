<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Parts\RangeDispatchEntry;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use stdClass;

class RangeDispatchItem extends AbstractItem
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static
    {
        if (!isset($data->property) || !is_string($data->property)) {
            throw new InvalidItemDefinitionException("Range dispatch item must have a property");
        }

        if (!isset($data->entries) || !is_array($data->entries)) {
            throw new InvalidItemDefinitionException("Range dispatch item must have an entries array");
        }

        $entries = [];
        foreach ($data->entries as $entry) {
            if (!($entry instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Entry must be an object");
            }
            if (!isset($entry->threshold) || !is_numeric($entry->threshold)) {
                throw new InvalidItemDefinitionException("Entry must have a numeric threshold");
            }
            if (!isset($entry->model) || !($entry->model instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Entry must have a model object");
            }
            $entries[] = new RangeDispatchEntry($entry->threshold, ItemType::createFromData($entry->model, $resourceManager, $properties));
        }

        $scale = 1.0;
        if (isset($data->scale) && is_numeric($data->scale)) {
            $scale = floatval($data->scale);
        }

        if (isset($data->fallback) && $data->fallback instanceof stdClass) {
            $fallback = ItemType::createFromData($data->fallback, $resourceManager, $properties);
        } else {
            $fallback = ModelItem::createUnknown($resourceManager, $properties);
        }

        return new static($properties, $data->property, $entries, $fallback, $scale, $data);
    }

    /**
     * @param Properties $properties
     * @param string $property
     * @param RangeDispatchEntry[] $entries
     * @param ItemInterface $fallback
     * @param float $scale
     * @param stdClass $options
     */
    public function __construct(
        Properties $properties,
        protected string $property,
        protected array $entries,
        protected ItemInterface $fallback,
        protected float $scale = 1,
        protected stdClass $options
    )
    {
        parent::__construct($properties);
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        $value = $this->getProperties()->getNumber($this->property)->get($this->options);
        $value *= $this->scale;
        foreach ($this->entries as $entry) {
            if ($entry->match($value)) {
                return $entry->getItem()->render($width, $height);
            }
        }

        return $this->fallback->render($width, $height);
    }
}
