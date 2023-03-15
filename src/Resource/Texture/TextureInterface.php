<?php

namespace Aternos\Renderchest\Resource\Texture;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Imagick;

interface TextureInterface
{
    /**
     * @param int $animationTick
     * @return Imagick
     */
    public function getImage(int $animationTick = 0): Imagick;

    /**
     * Get the total length of the animation in global animation ticks
     *
     * @return int
     * @throws TextureResolutionException
     */
    public function getAnimationTickLength(): int;

    /**
     * Different frames may be displayed for different amounts of time.
     * This method resolves what texture frame should be shown based on the global tick number.
     *
     * @param int $animationTick
     * @return ResolvedAnimationFrame
     */
    public function resolveAnimationFrame(int $animationTick): ResolvedAnimationFrame;

    /**
     * @return TextureMeta
     */
    public function getMeta(): TextureMeta;
}
