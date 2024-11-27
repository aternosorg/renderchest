<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Imagick;
use stdClass;

class ConditionItem extends AbstractItem
{
    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager, Properties $properties): static
    {
        if (!isset($data->property) || !is_string($data->property)) {
            throw new InvalidItemDefinitionException("Condition item must have a property");
        }

        if (!isset($data->on_true) || !($data->on_true instanceof stdClass)) {
            throw new InvalidItemDefinitionException("Condition item must have an on_true object");
        }

        if (!isset($data->on_false) || !($data->on_false instanceof stdClass)) {
            throw new InvalidItemDefinitionException("Condition item must have an on_false object");
        }

        $trueItem = ItemType::createFromData($data->on_true, $resourceManager, $properties);
        $falseItem = ItemType::createFromData($data->on_false, $resourceManager, $properties);
        return new static($properties, $trueItem, $falseItem, $data->property, $data);
    }

    /**
     * @param Properties $properties
     * @param ItemInterface $trueItem
     * @param ItemInterface $falseItem
     * @param string $property
     * @param stdClass $options
     */
    public function __construct(
        Properties $properties,
        protected ItemInterface $trueItem,
        protected ItemInterface $falseItem,
        protected string $property,
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
        $value = $this->getProperties()->getCondition($this->property)->get($this->options);
        if ($value) {
            return $this->trueItem->render($width, $height);
        } else {
            return $this->falseItem->render($width, $height);
        }
    }
}
