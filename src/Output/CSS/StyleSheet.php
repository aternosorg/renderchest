<?php

namespace Aternos\Renderchest\Output\CSS;

class StyleSheet
{
    /**
     * @var CSSEntry[]
     */
    protected array $entries = [];

    /**
     * @param array $entries
     * @return $this
     */
    public function setEntries(array $entries): static
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return array
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param CSSEntry $entry
     * @return $this
     */
    public function addEntry(CSSEntry $entry): static
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * @param CSSEntry[] $entries
     * @return $this
     */
    public function addEntries(array $entries): static
    {
        array_push($this->entries, ...$entries);
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $res = [];
        foreach ($this->entries as $entry) {
            $res[] = $entry->getEntryString();
        }
        return implode("\n\n", $res);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContent();
    }
}