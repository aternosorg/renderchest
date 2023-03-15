<?php

namespace Aternos\Renderchest\Output\CSS;

class PropertyListEntry extends CSSEntry
{
    /**
     * @var string[]
     */
    protected array $properties = [];

    public function __construct(protected string $selector)
    {
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties): static
    {
        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @param string|int|float $value
     * @return $this
     */
    public function setProperty(string $name, string|int|float $value): static
    {
        $this->properties[$name] = strval($value);
        return $this;
    }

    /**
     * @param string $selector
     * @return $this
     */
    public function setSelector(string $selector): static
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getContent(): string
    {
        $res = [];
        foreach ($this->properties as $name => $value) {
            $res[] = $name . ": " . $value . ";";
        }
        return implode("\n", $res);
    }

    /**
     * @inheritDoc
     */
    protected function getSelector(): string
    {
        return $this->selector;
    }
}
