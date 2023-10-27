<?php

namespace Aternos\Renderchest\Output;

use Aternos\Renderchest\Exception\TextureResolutionException;
use Aternos\Renderchest\Output\CSS\MediaQueryEntry;
use Aternos\Renderchest\Output\CSS\StyleSheet;
use Aternos\Renderchest\Output\ItemStyle\ArmorItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\DecoratedPotItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\DefaultItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\EnchantedItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\InternalItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\ItemStyleGenerator;
use Aternos\Renderchest\Output\ItemStyle\PotionItemStyleGenerator;
use Aternos\Renderchest\Resource\FolderResourceManager;
use Aternos\Renderchest\Resource\ResourceLocator;
use Aternos\Taskmaster\Taskmaster;
use Closure;
use Exception;
use ImagickException;

class ItemLibraryGenerator
{
    /**
     * @var class-string<ItemStyleGenerator>[]
     */
    const STYLE_GENERATORS = [
        InternalItemStyleGenerator::class,
        DecoratedPotItemStyleGenerator::class,
        ArmorItemStyleGenerator::class,
        PotionItemStyleGenerator::class,
        EnchantedItemStyleGenerator::class,
        DefaultItemStyleGenerator::class
    ];

    protected array $namespaces = ["minecraft"];
    protected int $size = 64;
    protected int $quality = 2;
    protected bool $createItemList = false;
    protected bool $createPngFallback = false;
    protected string $prefix = "rc-";
    protected string $format = "png";
    protected ?string $itemListFilename = null;
    protected ?string $cssFilename = null;
    protected ?string $enchantmentFilename = null;
    protected array $enchantmentSelectors = [];

    /**
     * @var Item[]
     */
    protected array $items = [];

    protected FolderResourceManager $resourceManager;

    /**
     * @param string[] $assets
     * @param string $output
     */
    public function __construct(protected array $assets, protected string $output)
    {
        $this->resourceManager = new FolderResourceManager($this->assets);
    }


    /**
     * @param Closure|callable|null $onProgress
     * @return $this
     * @throws ImagickException
     * @throws Exception
     * @throws TextureResolutionException
     */
    public function render(Closure|callable|null $onProgress = null): static
    {
        $enchantedEffect = $this->resourceManager->getTexture(ResourceLocator::parse("minecraft:misc/enchanted_glint_item"))->getImage();
        $this->items = $this->createItems($this->size, $this->quality, $onProgress);
        $style = new StyleSheet();
        $fallback = new MediaQueryEntry("min-resolution: 1dpi");

        /** @var class-string<ItemStyleGenerator> $styleGeneratorClass */
        foreach (static::STYLE_GENERATORS as $styleGeneratorClass) {
            $style->addEntries($styleGeneratorClass::getGlobalStyles($this));
            if ($this->createPngFallback) {
                $fallback->addEntries($styleGeneratorClass::getGlobalFallbackStyles($this));
            }
            $this->addEnchantmentSelectors(...$styleGeneratorClass::getEnchantmentSelectors($this));
        }

        foreach ($this->items as $item) {
            $styleGenerator = $this->getItemStyleGenerator($item);
            $style->addEntries($styleGenerator->getItemStyles());
            if ($this->createPngFallback) {
                $fallback->addEntries($styleGenerator->getItemFallbackStyles());
            }
        }

        /** @var class-string<ItemStyleGenerator> $styleGeneratorClass */
        foreach (static::STYLE_GENERATORS as $styleGeneratorClass) {
            $style->addEntries($styleGeneratorClass::getGlobalPostStyles($this));
            if ($this->createPngFallback) {
                $fallback->addEntries($styleGeneratorClass::getGlobalPostFallbackStyles($this));
            }
        }

        file_put_contents($this->output . "/" . $this->getEnchantmentFilename(), $enchantedEffect);
        if ($this->createPngFallback) {
            $enchantedEffect->setFormat("png");
            file_put_contents($this->output . "/" . $this->getEnchantmentFilename("png"), $enchantedEffect);
            $style->addEntry($fallback);
        }

        file_put_contents($this->output . "/" . $this->getCssFilename(), $style);

        if ($this->createItemList) {
            file_put_contents($this->output . "/" . $this->getItemListFilename(), json_encode(array_keys($this->items)));
        }

        return $this;
    }

    /**
     * @param Item $item
     * @return ItemStyleGenerator
     * @throws Exception
     */
    protected function getItemStyleGenerator(Item $item): ItemStyleGenerator
    {
        foreach (static::STYLE_GENERATORS as $styleGenerator) {
            /** @var class-string<ItemStyleGenerator> $generator */
            $generator = $styleGenerator;
            if ($generator::hasItemStyle($item)) {
                return new $generator($item);
            }
        }
        throw new Exception("No supported item style generator found");
    }

    /**
     * @return string[]
     */
    protected function getItemNames(): array
    {
        $itemNames = ["minecraft:item/unknown", "minecraft:item/empty"];
        foreach ($this->namespaces as $namespace) {
            foreach ($this->resourceManager->getAllItems($namespace) as $name) {
                if (preg_match("#_\d\d$#", $name) && !str_contains($name, "minecraft:item/music_disc_")) {
                    continue;
                }
                if (!in_array($name, $itemNames, true)) {
                    $itemNames[] = $name;
                }
            }
        }
        return $itemNames;
    }

    /**
     * @param int $size
     * @param int $quality
     * @param Closure|callable|null $onProgress
     * @return Item[]
     * @throws Exception
     */
    protected function createItems(int $size, int $quality = 1, Closure|callable|null $onProgress = null): array
    {
        $items = $this->getItemNames();

        $taskmaster = new Taskmaster();
        $taskmaster->autoDetectWorkers(12);

        foreach ($items as $itemName) {
            $taskmaster->runTask(new ItemRenderTask($itemName, $size, $quality, $this->assets, $this->format, $this->createPngFallback, $this->output));
        }

        $total = count($items);
        $results = [];
        $i = 0;

        foreach ($taskmaster->waitAndHandleTasks() as $task) {
            if (!$task instanceof ItemRenderTask) {
                continue;
            }
            if ($task->getError() || $task->getResult() === null) {
                echo "Failed to render " . $task->getItemName();
                if ($task->getError()) {
                    echo ": " . $task->getError()->getMessage();
                }
                echo PHP_EOL;
            } else {
                $results[$task->getResult()] = new Item($task->getResult(), $this);
            }

            $i++;
            if ($onProgress !== null) {
                $onProgress($i, $total, $task->getItemName());
            }
        }

        $taskmaster->stop();

        return $results;
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param array $namespaces
     * @return $this
     */
    public function setNamespaces(array $namespaces): static
    {
        $this->namespaces = $namespaces;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     * @return $this
     */
    public function setQuality(int $quality): static
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param string $name
     * @return Item|null
     */
    public function getItem(string $name): ?Item
    {
        return $this->items[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param string $name
     * @param bool $fallbackTexture
     * @return string
     */
    public function getItemCSSUrl(string $name, bool $fallbackTexture = false): string
    {
        $item = $this->getItem($name) ?? $this->getItem("minecraft:unknown");
        $format = !$fallbackTexture && $this->createPngFallback ? "png" : $this->format;
        return "url('" . $item->getImageFilePath($format) . "')";
    }

    /**
     * @param bool $fallbackTexture
     * @return string
     */
    public function getEnchantmentUrl(bool $fallbackTexture = false): string
    {
        $format = !$fallbackTexture && $this->createPngFallback ? "png" : $this->format;
        return "url('" . $this->getEnchantmentFilename($format) . "')";
    }

    /**
     * @param string $filename
     * @param string $extension
     * @return string
     */
    protected function ensureFileExtension(string $filename, string $extension): string
    {
        if (!str_starts_with($extension, ".")) {
            $extension = "." . $extension;
        }

        if (!str_ends_with($filename, $extension)) {
            $filename .= $extension;
        }
        return $filename;
    }

    /**
     * @param string|null $itemListFilename
     * @return $this
     */
    public function setItemListFilename(?string $itemListFilename): static
    {
        $this->itemListFilename = $itemListFilename;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getItemListFilename(): ?string
    {
        if ($this->itemListFilename === null) {
            return $this->prefix . $this->namespaces[0] . ".json";
        }
        return $this->ensureFileExtension($this->itemListFilename, ".json");
    }

    /**
     * @param string|null $cssFilename
     * @return $this
     */
    public function setCssFilename(?string $cssFilename): static
    {
        $this->cssFilename = $cssFilename;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCssFilename(): ?string
    {
        if ($this->cssFilename === null) {
            return $this->prefix . $this->namespaces[0] . "-" . $this->size . ".css";
        }
        return $this->ensureFileExtension($this->cssFilename, ".css");
    }

    /**
     * @param string|null $enchantmentFilename
     * @return $this
     */
    public function setEnchantmentFilename(?string $enchantmentFilename): static
    {
        $this->enchantmentFilename = $enchantmentFilename;
        return $this;
    }

    /**
     * @param string|null $format
     * @return string|null
     */
    public function getEnchantmentFilename(?string $format = null): ?string
    {
        if ($this->enchantmentFilename === null) {
            return $this->prefix . "enchantment." . ($format ?? $this->format);
        }
        return $this->ensureFileExtension($this->enchantmentFilename, $format ?? $this->format);
    }

    /**
     * @return array
     */
    public function getEnchantmentSelectors(): array
    {
        return $this->enchantmentSelectors;
    }

    /**
     * @param string ...$selectors
     * @return $this
     */
    public function addEnchantmentSelectors(string ...$selectors): static
    {
        array_push($this->enchantmentSelectors, ...$selectors);
        return $this;
    }

    /**
     * @param bool $createItemList
     * @return $this
     */
    public function setCreateItemList(bool $createItemList): static
    {
        $this->createItemList = $createItemList;
        return $this;
    }

    /**
     * @param bool $createPngFallback
     * @return $this
     */
    public function setCreatePngFallback(bool $createPngFallback): static
    {
        $this->createPngFallback = $createPngFallback;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCreatePngFallback(): bool
    {
        return $this->createPngFallback;
    }
}
