<?php

namespace Aternos\Renderchest\Resource;

use Aternos\Renderchest\Exception\InvalidResourceLocatorException;

class ResourceLocator
{
    /**
     * @param string $locator
     * @param string|null $namespace
     * @return ResourceLocator
     * @throws InvalidResourceLocatorException
     */
    public static function parse(string $locator, ?string $namespace = null): ResourceLocator
    {
        if (!preg_match("#^(?:(\w+):)?(.*)$#", $locator, $matches)) {
            throw new InvalidResourceLocatorException("Invalid resource locator '" . $locator . "'");
        }
        $namespace = $matches[1] ?: ($namespace ?: "minecraft");
        $path = $matches[2];

        return new static($namespace, $path);
    }

    /**
     * @param string $namespace
     * @param string $path
     */
    public function __construct(protected string $namespace, protected string $path)
    {
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $locator
     * @return bool
     * @throws InvalidResourceLocatorException
     */
    public function isString(string $locator): bool
    {
        $loc = static::parse($locator);
        return $loc->getNamespace() == $this->getNamespace() && $loc->getPath() == $this->getPath();
    }

    /**
     * @param ResourceLocator $locator
     * @return bool
     */
    public function is(ResourceLocator $locator): bool
    {
        return $locator->getNamespace() == $this->getNamespace() && $locator->getPath() == $this->getPath();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getNamespace() . ":" . $this->getPath();
    }

    /**
     * @return ResourceLocator
     */
    public function clone(): ResourceLocator
    {
        return new static($this->getNamespace(), $this->getPath());
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }
}
