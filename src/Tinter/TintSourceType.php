<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Exception\InvalidTinterDefinitionException;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use stdClass;

enum TintSourceType : string
{
    case Constant = "minecraft:constant";
    case Dye = "minecraft:dye";
    case Grass = "minecraft:grass";
    case Firework = "minecraft:firework";
    case Potion = "minecraft:potion";
    case MapColor = "minecraft:map_color";
    case Team = "minecraft:team";
    case CustomModelData = "minecraft:custom_model_data";
    case Random = "renderchest:random";

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return Tinterface
     * @throws InvalidTinterDefinitionException
     */
    public static function createFromData(stdClass $data, ResourceManagerInterface $resourceManager): Tinterface
    {
        $typeString = $data->type ?? null;
        if (!is_string($typeString)) {
            throw new InvalidTinterDefinitionException("Tinter type must be a string");
        }
        $type = self::tryFrom($typeString);
        if ($type === null) {
            throw new InvalidTinterDefinitionException("Invalid tinter type: " . $typeString);
        }
        return $type->create($data, $resourceManager);
    }

    /**
     * @param stdClass $data
     * @param ResourceManagerInterface $resourceManager
     * @return Tinterface
     * @throws InvalidTinterDefinitionException
     */
    public function create(stdClass $data, ResourceManagerInterface $resourceManager): Tinterface
    {
        return match ($this) {
            self::Constant => ConstantTinter::fromData($data, $resourceManager),
            self::Dye => DyeTinter::fromData($data, $resourceManager),
            self::Grass => GrassTinter::fromData($data, $resourceManager),
            self::Firework => FireworkTinter::fromData($data, $resourceManager),
            self::Potion => PotionTinter::fromData($data, $resourceManager),
            self::MapColor => MapColorTinter::fromData($data, $resourceManager),
            self::Team => TeamTinter::fromData($data, $resourceManager),
            self::CustomModelData => CustomModelDataTinter::fromData($data, $resourceManager),
            self::Random => RandomTinter::fromData($data, $resourceManager),
        };
    }
}
