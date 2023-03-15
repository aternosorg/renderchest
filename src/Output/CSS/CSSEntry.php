<?php

namespace Aternos\Renderchest\Output\CSS;

abstract class CSSEntry
{
    /**
     * @return string
     */
    public function getEntryString(): string
    {
        return $this->getSelector() . " {\n" . $this->indent($this->getContent()) . "\n}";
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getEntryString();
    }

    /**
     * @return string
     */
    abstract protected function getSelector(): string;

    /**
     * @return string
     */
    abstract protected function getContent(): string;

    /**
     * @param string $str
     * @return string
     */
    protected function indent(string $str): string
    {
        $parts = explode("\n", $str);
        $parts = array_map(function ($part) {
            return "    " . $part;
        }, $parts);
        return implode("\n", $parts);
    }
}
