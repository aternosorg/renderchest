<?php

namespace Aternos\Renderchest\Resource;

/**
 * Interface DynamicResourceGenerator
 * A DynamicResourceGenerator takes over a specific namespace from the ResourceManager and generates resources on the fly
 */
interface DynamicResourceGeneratorInterface extends ResourceManagerInterface
{
    /**
     * Get the namespace this generator is responsible for
     *
     * @return string
     */
    public static function getNamespace(): string;

    /**
     * @param ResourceManagerInterface $resourceManager
     */
    public function __construct(ResourceManagerInterface $resourceManager);
}
