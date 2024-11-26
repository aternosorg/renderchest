<?php

namespace Aternos\Renderchest\Resource\Item;

use Aternos\Renderchest\Exception\InvalidItemDefinitionException;
use Aternos\Renderchest\Resource\Item\Parts\SelectCase;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Aternos\Renderchest\Tinter\TinterList;
use Exception;
use Imagick;
use stdClass;

class SelectItem implements ItemInterface
{
    const PROPERTIES = [
        "minecraft:main_hand" => "right",
        "minecraft:charge_type" => "none",
        "minecraft:trim_material" => "none",
        "minecraft:block_state" => "",
        "minecraft:display_context" => "gui",
        "minecraft:local_time" => "", //TODO: actually get time
        "minecraft:context_dimension" => "minecraft:overworld",
        "minecraft:context_entity_type" => "minecraft:player",
        "minecraft:custom_model_data" => ""
    ];

    /**
     * @inheritDoc
     */
    public static function fromData(stdClass $data, ResourceManagerInterface $resourceManager): static
    {
        if (!isset($data->property) || !is_string($data->property)) {
            throw new InvalidItemDefinitionException("Condition item must have a property");
        }

        if (!isset($data->cases) || !is_array($data->cases)) {
            throw new InvalidItemDefinitionException("Select item must have cases");
        }

        $cases = [];
        foreach ($data->cases as $case) {
            if (!($case instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Case must be an object");
            }

            if (!isset($case->when) || (!is_string($case->when) && !is_array($case->when))) {
                throw new InvalidItemDefinitionException("Case must have a when property");
            }

            if (!isset($case->model) || !($case->model instanceof stdClass)) {
                throw new InvalidItemDefinitionException("Case must have a model object");
            }

            $when = is_array($case->when) ? $case->when : [$case->when];

            $cases[] = new SelectCase($when, ItemType::createFromData($case->model, $resourceManager));
        }

        if (isset($data->fallback) && $data->fallback instanceof stdClass) {
            $fallback = ItemType::createFromData($data->fallback, $resourceManager);
        } else {
            $fallback = ModelItem::createUnknown($resourceManager);
        }

        return new static($data->property, $cases, $fallback);
    }

    /**
     * @param string $property
     * @param SelectCase[] $cases
     * @param ItemInterface $fallback
     */
    public function __construct(
        protected string $property,
        protected array $cases,
        protected ItemInterface $fallback
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function render(int $width, int $height): Imagick
    {
        $value = static::PROPERTIES[$this->property] ?? "";
        foreach ($this->cases as $case) {
            if ($case->match($value)) {
                return $case->getItem()->render($width, $height);
            }
        }

        return $this->fallback->render($width, $height);
    }
}
