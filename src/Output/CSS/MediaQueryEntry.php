<?php

namespace Aternos\Renderchest\Output\CSS;

class MediaQueryEntry extends CSSEntry
{
    /**
     * @var CSSEntry[]
     */
    protected array $entries;

    /**
     * @param string $test
     */
    public function __construct(protected string $test)
    {
    }

    /**
     * @inheritDoc
     */
    protected function getSelector(): string
    {
        return "@media (" . $this->test . ")";
    }

    /**
     * @inheritDoc
     */
    protected function getContent(): string
    {
        $res = [];
        foreach ($this->entries as $entry) {
            $res[] = $entry->getEntryString();
        }
        return implode("\n", $res);
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
        foreach ($entries as $entry) {
            $this->addEntry($entry);
        }
        return $this;
    }
}
