<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Parts\SelectCase;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use stdClass;

class SelectItem extends AbstractItem
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static
    {
        if (!isset($data->property) || !is_string($data->property)) {
            throw new InvalidItemDefinitionException("Select item must have a property");
        }

        if (!isset($data->cases) || !is_array($data->cases)) {
            throw new InvalidItemDefinitionException("Select item must have cases");
        }

        $cases = [];
        foreach ($data->cases as $case) {
            if (!($case instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Case must be an object");
            }

            if (!isset($case->when) || (!is_string($case->when) && !is_array($case->when))) {
                throw new InvalidItemDefinitionException("Case must have a when property");
            }

            if (!isset($case->model) || !($case->model instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Case must have a model object");
            }

            $when = is_array($case->when) ? $case->when : [$case->when];

            $cases[] = new SelectCase($when, ItemType::createFromData($case->model, $resourceManager, $properties));
        }

        if (isset($data->fallback) && $data->fallback instanceof stdClass) {
            $fallback = ItemType::createFromData($data->fallback, $resourceManager, $properties);
        } else {
            $fallback = ModelItem::createUnknown($resourceManager, $properties);
        }

        return new static($properties, $data->property, $cases, $fallback, $data);
    }

    /**
     * @param Properties $properties
     * @param string $property
     * @param SelectCase[] $cases
     * @param ItemInterface $fallback
     * @param stdClass $options
     */
    public function __construct(
        Properties $properties,
        protected string $property,
        protected array $cases,
        protected ItemInterface $fallback,
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
        $value = $this->getProperties()->getString($this->property)->get($this->options);
        foreach ($this->cases as $case) {
            if ($case->match($value)) {
                return $case->getItem()->render($width, $height);
            }
        }

        return $this->fallback->render($width, $height);
    }
}
