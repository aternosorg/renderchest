<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Exception\InvalidTransformationException;
use Aternos\Renderchest\Resource\Item\Properties\Properties;
use Aternos\Renderchest\Vector\Matrix4;

abstract class AbstractItem implements ItemInterface
{
    public function __construct(
        protected Properties $properties
    )
    {
    }

    /**
     * @param mixed $field
     * @return Matrix4
     * @throws InvalidItemDefinitionException
     */
    protected static function parseTransformation(mixed $field): Matrix4
    {
        if (!is_array($field) && !is_object($field)) {
            throw new InvalidItemDefinitionException("Transformation must be an array or object");
        }

        try {
            return Matrix4::fromData($field);
        } catch (InvalidTransformationException $e) {
            throw new InvalidItemDefinitionException("Invalid transformation data", previous: $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getProperties(): Properties
    {
        return $this->properties;
    }
}
