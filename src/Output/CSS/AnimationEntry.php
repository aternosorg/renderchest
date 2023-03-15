<?php

namespace Aternos\Renderchest\Output\CSS;

class AnimationEntry extends CSSEntry
{
    /**
     * @var PropertyListEntry[]
     */
    protected array $keyframes;

    public function __construct(protected string $name)
    {
    }

    /**
     * @inheritDoc
     */
    protected function getSelector(): string
    {
        return "@keyframes " . $this->name;
    }

    /**
     * @param PropertyListEntry $entry
     * @return $this
     */
    public function addKeyframe(PropertyListEntry $entry): static
    {
        $this->keyframes[] = $entry;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getContent(): string
    {
        $res = [];
        foreach ($this->keyframes as $frame) {
            $res[] = $frame->getEntryString();
        }
        return implode("\n", $res);
    }
}
