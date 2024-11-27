<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

class Properties
{
    /**
     * @var ConditionProperty[]
     */
    protected array $conditionProperties = [];

    /**
     * @var StringProperty[]
     */
    protected array $stringProperties = [];

    /**
     * @var NumberProperty[]
     */
    protected array $numberProperties = [];

    public function __construct()
    {
        $this->set(new LocalTimeProperty());
    }

    /**
     * @param Property $property
     * @return $this
     */
    public function set(Property $property): static
    {
        $name = $property->getName();
        if ($property instanceof ConditionProperty) {
            $this->conditionProperties[$name] = $property;
        } elseif ($property instanceof NumberProperty) {
            $this->numberProperties[$name] = $property;
        } elseif ($property instanceof StringProperty) {
            $this->stringProperties[$name] = $property;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return ConditionProperty
     */
    public function getCondition(string $name): ConditionProperty
    {
        return $this->conditionProperties[$name] ?? new ConditionProperty($name);
    }

    /**
     * @param string $name
     * @return NumberProperty
     */
    public function getNumber(string $name): NumberProperty
    {
        return $this->numberProperties[$name] ?? new NumberProperty($name);
    }

    /**
     * @param string $name
     * @return StringProperty
     */
    public function getString(string $name): StringProperty
    {
        return $this->stringProperties[$name] ?? new StringProperty($name);
    }
}
