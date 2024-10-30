<?php

namespace Aternos\Renderchest\Resource;

/**
 * Interface DynamicResourceGenerator
 * A DynamicResourceGenerator takes over a specific namespace from the ResourceManager and generates resources on the fly
 */
interface DynamicResourceGeneratorInterface extends ResourceManagerInterface
{
    /**
     * @param ResourceManagerInterface $resourceManager
     */
    public function __construct(ResourceManagerInterface $resourceManager);

    /**
     * Get the namespace this generator is responsible for
     *
     * @return string
     */
    public function getNamespace(): string;
}
