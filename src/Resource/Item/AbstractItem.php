<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Resource\Item\Properties\Properties;

abstract class AbstractItem implements ItemInterface
{
    public function __construct(
        protected Properties $properties
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getProperties(): Properties
    {
        return $this->properties;
    }
}
