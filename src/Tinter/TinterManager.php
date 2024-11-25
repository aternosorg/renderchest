<?php

namespace Aternos\Renderchest\Tinter;

use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Renderchest\Resource\ResourceManagerInterface;
use Exception;

class TinterManager
{
    public function __construct(protected ResourceManagerInterface $resourceManager)
    {
    }

    /**
     * @param ResourceLocator $modelLocator
     * @return Tinterface|null
     * @throws Exception
     */
    public function getTinter(ResourceLocator $modelLocator): ?Tinterface
    {
        if ($modelLocator->isString("mobheads:item/tropical_fish")) {
            return new FishTinter();
        }

        if ($modelLocator->getNamespace() !== "minecraft") {
            return null;
        }

        if (in_array($modelLocator->getPath(), [
            "block/oak_leaves",
            "block/birch_leaves",
            "block/acacia_leaves",
            "block/jungle_leaves",
            "block/spruce_leaves",
            "block/dark_oak_leaves",
            "block/mangrove_leaves",
            "item/lily_pad",
            "item/grass",
            "item/short_grass",
            "item/tall_grass",
            "item/fern",
            "item/large_fern",
            "item/vine"
        ])) {
            return new FoliageTinter($this->resourceManager);
        }

        return match ($modelLocator->getPath()) {
            "block/grass_block" => new GrassTinter($this->resourceManager),

            "item/potion", "item/splash_potion", "item/lingering_potion" => new ConstantTinter("#375cc4"),

            "item/allay_spawn_egg" => new ConstantTinter("#00d7fc", "#00abfc"),
            "item/armadillo_spawn_egg" => new ConstantTinter("#a67775", "#734b4f"),
            "item/axolotl_spawn_egg" => new ConstantTinter("#f8bfe0", "#a42c73"),
            "item/bat_spawn_egg" => new ConstantTinter("#4b3d2f", "#0f0f0f"),
            "item/bee_spawn_egg" => new ConstantTinter("#eac142", "#42241b"),
            "item/blaze_spawn_egg" => new ConstantTinter("#f3b001", "#fcf57d"),
            "item/bogged_spawn_egg" => new ConstantTinter("#8a9c72", "#314d1b"),
            "item/breeze_spawn_egg" => new ConstantTinter("#af94df", "#9166df"),
            "item/cat_spawn_egg" => new ConstantTinter("#ecc68c", "#937155"),
            "item/cave_spider_spawn_egg" => new ConstantTinter("#0c414d", "#a60e0e"),
            "item/chicken_spawn_egg" => new ConstantTinter("#9f9f9f", "#fc0000"),
            "item/cod_spawn_egg" => new ConstantTinter("#bfa569", "#e2c289"),
            "item/cow_spawn_egg" => new ConstantTinter("#433526", "#9f9f9f"),
            "item/creaking_spawn_egg" => new ConstantTinter("#5f5f5f", "#fc7812"),
            "item/creeper_spawn_egg" => new ConstantTinter("#0da50b", "#000000"),
            "item/dolphin_spawn_egg" => new ConstantTinter("#223a4c", "#f6f6f6"),
            "item/donkey_spawn_egg" => new ConstantTinter("#524438", "#847465"),
            "item/drowned_spawn_egg" => new ConstantTinter("#8deed4", "#789a64"),
            "item/elder_guardian_spawn_egg" => new ConstantTinter("#cccab8", "#737591"),
            "item/enderman_spawn_egg" => new ConstantTinter("#161616", "#000000"),
            "item/endermite_spawn_egg" => new ConstantTinter("#161616", "#6d6d6d"),
            "item/evoker_spawn_egg" => new ConstantTinter("#939999", "#1e1c1a"),
            "item/fox_spawn_egg" => new ConstantTinter("#d2b49d", "#ca6820"),
            "item/frog_spawn_egg" => new ConstantTinter("#ce7343", "#fcc57b"),
            "item/ghast_spawn_egg" => new ConstantTinter("#f6f6f6", "#bababa"),
            "item/glow_squid_spawn_egg" => new ConstantTinter("#095555", "#83eeba"),
            "item/goat_spawn_egg" => new ConstantTinter("#a3927b", "#54483d"),
            "item/guardian_spawn_egg" => new ConstantTinter("#598071", "#ee7c2f"),
            "item/hoglin_spawn_egg" => new ConstantTinter("#c46d54", "#5e6363"),
            "item/horse_spawn_egg" => new ConstantTinter("#be9c7c", "#ebe200"),
            "item/husk_spawn_egg" => new ConstantTinter("#786f60", "#e3ca92"),
            "item/llama_spawn_egg" => new ConstantTinter("#be9c7c", "#975e3f"),
            "item/magma_cube_spawn_egg" => new ConstantTinter("#330000", "#f9f900"),
            "item/mooshroom_spawn_egg" => new ConstantTinter("#9e0f10", "#b5b5b5"),
            "item/mule_spawn_egg" => new ConstantTinter("#1b0200", "#50321d"),
            "item/ocelot_spawn_egg" => new ConstantTinter("#ecdb7c", "#554333"),
            "item/panda_spawn_egg" => new ConstantTinter("#e4e4e4", "#1b1b22"),
            "item/parrot_spawn_egg" => new ConstantTinter("#0da50b", "#fc0000"),
            "item/phantom_spawn_egg" => new ConstantTinter("#425088", "#86fc00"),
            "item/pig_spawn_egg" => new ConstantTinter("#eda3a0", "#d8625e"),
            "item/piglin_spawn_egg" => new ConstantTinter("#975e3f", "#f6f0a2"),
            "item/piglin_brute_spawn_egg" => new ConstantTinter("#582910", "#f6f0a2"),
            "item/pillager_spawn_egg" => new ConstantTinter("#522e35", "#939999"),
            "item/polar_bear_spawn_egg" => new ConstantTinter("#efefef", "#93938e"),
            "item/pufferfish_spawn_egg" => new ConstantTinter("#f3b001", "#36c1ef"),
            "item/rabbit_spawn_egg" => new ConstantTinter("#975e3f", "#724730"),
            "item/ravager_spawn_egg" => new ConstantTinter("#74736f", "#5a4f48"),
            "item/salmon_spawn_egg" => new ConstantTinter("#9e0f10", "#0e8273"),
            "item/sheep_spawn_egg" => new ConstantTinter("#e4e4e4", "#fcb3b3"),
            "item/shulker_spawn_egg" => new ConstantTinter("#926692", "#4c3751"),
            "item/silverfish_spawn_egg" => new ConstantTinter("#6d6d6d", "#2f2f2f"),
            "item/skeleton_spawn_egg" => new ConstantTinter("#bfbfbf", "#484848"),
            "item/skeleton_horse_spawn_egg" => new ConstantTinter("#67674e", "#e2e2d5"),
            "item/slime_spawn_egg" => new ConstantTinter("#509e3d", "#7dbd6d"),
            "item/spider_spawn_egg" => new ConstantTinter("#332c27", "#a60e0e"),
            "item/squid_spawn_egg" => new ConstantTinter("#223a4c", "#6f8697"),
            "item/stray_spawn_egg" => new ConstantTinter("#607576", "#dae7e7"),
            "item/strider_spawn_egg" => new ConstantTinter("#9a3335", "#4c484c"),
            "item/tadpole_spawn_egg" => new ConstantTinter("#6c523c", "#160a00"),
            "item/trader_llama_spawn_egg" => new ConstantTinter("#e7a22f", "#446194"),
            "item/tropical_fish_spawn_egg" => new ConstantTinter("#ec6815", "#fcf6ec"),
            "item/turtle_spawn_egg" => new ConstantTinter("#e4e4e4", "#00adad"),
            "item/vex_spawn_egg" => new ConstantTinter("#798ea2", "#e5eaee"),
            "item/villager_spawn_egg" => new ConstantTinter("#553b32", "#bb8971"),
            "item/vindicator_spawn_egg" => new ConstantTinter("#939999", "#275d60"),
            "item/wandering_trader_spawn_egg" => new ConstantTinter("#446194", "#e7a22f"),
            "item/warden_spawn_egg" => new ConstantTinter("#0f4548", "#38d3dd"),
            "item/witch_spawn_egg" => new ConstantTinter("#330000", "#509e3d"),
            "item/wither_skeleton_spawn_egg" => new ConstantTinter("#141414", "#464c4c"),
            "item/wolf_spawn_egg" => new ConstantTinter("#d4d0d0", "#ccad94"),
            "item/zoglin_spawn_egg" => new ConstantTinter("#c46d54", "#e3e3e3"),
            "item/zombie_spawn_egg" => new ConstantTinter("#00adad", "#789a64"),
            "item/zombie_horse_spawn_egg" => new ConstantTinter("#305133", "#95c082"),
            "item/zombie_villager_spawn_egg" => new ConstantTinter("#553b32", "#789a64"),
            "item/zombified_piglin_spawn_egg" => new ConstantTinter("#e79191", "#4b7028"),
            "item/camel_spawn_egg" => new ConstantTinter("#f9c168", "#c99136"),
            "item/ender_dragon_spawn_egg" => new ConstantTinter("#1c1c1c", "#dd78f7"),
            "item/wither_spawn_egg" => new ConstantTinter("#141414", "#4c719e"),
            "item/snow_golem_spawn_egg" => new ConstantTinter("#d6efef", "#7fa2a2"),
            "item/iron_golem_spawn_egg" => new ConstantTinter("#d8cbc0", "#73a131"),
            "item/sniffer_spawn_egg" => new ConstantTinter("#871e09", "#25ab70"),

            "item/white_banner" => new ConstantTinter("#f6fcfb"),
            "item/orange_banner" => new ConstantTinter("#f67e1d"),
            "item/magenta_banner" => new ConstantTinter("#c54dbb"),
            "item/light_blue_banner" => new ConstantTinter("#39b1d7"),
            "item/yellow_banner" => new ConstantTinter("#fbd53c"),
            "item/lime_banner" => new ConstantTinter("#7ec51f"),
            "item/pink_banner" => new ConstantTinter("#f089a8"),
            "item/gray_banner" => new ConstantTinter("#464e51"),
            "item/light_gray_banner" => new ConstantTinter("#9b9b95"),
            "item/cyan_banner" => new ConstantTinter("#169a9a"),
            "item/purple_banner" => new ConstantTinter("#8731b6"),
            "item/blue_banner" => new ConstantTinter("#3b43a8"),
            "item/brown_banner" => new ConstantTinter("#815331"),
            "item/green_banner" => new ConstantTinter("#5d7b16"),
            "item/red_banner" => new ConstantTinter("#ae2d26"),
            "item/black_banner" => new ConstantTinter("#1d1d21"),
            default => null
        };
    }
}
