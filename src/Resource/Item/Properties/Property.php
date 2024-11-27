<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

use stdClass;

abstract class Property
{
    /**
     * @param string $name
     */
    public function __construct(protected string $name)
    {
    }

    /**
     * @param stdClass $options
     * @return mixed
     */
    abstract public function get(stdClass $options): mixed;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
