<?php

namespace Aternos\Renderchest\Model\Face;

use Aternos\Renderchest\Vector\Vector3;

enum FaceDirection: string
{
    case UP = "up";
    case DOWN = "down";
    case NORTH = "north";
    case SOUTH = "south";
    case WEST = "west";
    case EAST = "east";

    public function getNormal(): Vector3
    {
        return match ($this) {
            self::UP => new Vector3(0, 1, 0),
            self::DOWN => new Vector3(0, -1, 0),
            self::NORTH => new Vector3(0, 0, -1),
            self::SOUTH => new Vector3(0, 0, 1),
            self::EAST => new Vector3(1, 0, 0),
            self::WEST => new Vector3(-1, 0, 0),
        };
    }
}
