# renderchest
Renderchest is a PHP library for rendering icons for Minecraft items directly from Minecraft's assets.
A CSS library for using the icons in web pages is also generated.

### Builtin models
While most block/item models in Minecraft are saved in Minecraft's JSON model format nowadays, some models are still hard-coded in Java (e.g. chests, shulker boxes).  
These models are inaccessible to the game's resource pack system and cannot be rendered by renderchest.
Renderchest therefore includes a number of built-in model files to replace these hard-coded models.

Renderchest also includes model files for the heads of most mobs.
These models can be used by rendering the `mobheads` namespace.

### Tinting
Some textures are dynamically tinted by the game (e.g. vegetation and grass blocks).
This information can't be extracted from the asset files and therefore is hard-coded in renderchest.

### Requirements
- PHP 8.1+
- ext-imagick
- ext-pcntl (optional, allows more efficient multithreading)

### Windows support
While Renderchest will generally work on Windows, it will be much slower since asynchronous tasks are not supported.
It is therefore recommended to use the [Windows Subsystem for Linux](https://learn.microsoft.com/en-us/windows/wsl/install) to run renderchest on Windows.

### Usage
To use renderchest, a valid Minecraft assets directory is required.
It can be extracted from a Minecraft client jar file.
### CLI usage
#### Installation
```bash
git clone https://github.com/aternosorg/renderchest.git
cd renderchest
composer install
```
#### Rendering icons
```bash
./renderchest --assets path/to/assets/root --output path/to/output/dir --namespace minecraft
```

```
OPTIONS
  --assets, -a       Assets folder to use. Multiple assets folders are possible,
                     but a base assets folder extracted from the Minecraft jar
                     should always be included.
  --fallback         Create a set of fallback textures as PNGs. Default: false
  --format, -f       Output image format. Default: webp
  --help, -?         Display this help.
  --item-list, -i    Create a JSON file containing the names of all rendered
                     items.
  --namespace, -n    Asset namespace that the items should be rendered from.
                     Default: minecraft
  --output, -o       Output directory
  --prefix, -p       Prefix to use for CSS classes. Default: rc-
  --quality, -q      When generating very small icons, small issues (like
                     z-fighting of faces close to each other) can occur. This
                     option allows rendering images in a higher resolution and
                     scaling them down for the final icon. Default: 2
  --size, -s         Size of the generated item icons. Default: 64
```
Renderchest uses [Taskmaster](https://github.com/aternosorg/taskmaster) for asynchronous tasks, which can be configured [using environment variables](https://github.com/aternosorg/taskmaster#defining-workers-using-environment-variables).

#### Using resource packs
Resource packs can be added by specifying multiple asset paths.
It is also important to always include the base assets folder extracted from the Minecraft jar.
```bash
./renderchest --assets path/to/resource-pack/assets --assets path/to/assets/root --output path/to/output/dir --namespace minecraft
```
Make sure to use the path to the assets folder within your resource pack, not the root of the resource pack.

### Using the generated CSS library
The generated CSS library can be used to display the rendered icons in web pages.
All generated CSS classes are prefixed with `rc-` by default.
Whenever a CSS class name contains a namespaced ID, the namespace is separated from the ID by an underscore (e.g. `minecraft:stone` -> `minecraft_stone`).
```html
<div class="rc-item rc-minecraft_magma_block" style="width: 64px; height: 64px"></div>
```
The `rc-item` class is required for all icons.
Additionally, a class for the item/block is required.

Enchanted items can be displayed by adding the `rc-enchanted` class.
```html
<div class="rc-item rc-minecraft_diamond_sword rc-enchanted" style="width: 64px; height: 64px"></div>
```

#### Armor trims
Armor trims can be displayed by adding a `rc-trim-[material]` class.
```html
<div class="rc-item rc-minecraft_diamond_helmet rc-trim-minecraft_gold" style="width: 64px; height: 64px"></div>
```

#### Decorated pots
Decorated pots can be displayed by adding `rc-pot-1-[material-1]` and `rc-pot-2-[material-3]` classes.
```html
<div class="rc-item rc-minecraft_decorated_pot rc-pot-1-minecraft_prize_pottery_sherd rc-pot-2-minecraft_angler_pottery_sherd" style="width: 64px; height: 64px"></div>
```
Only the first and third material are required because the other sides of the pot are not visible.

#### Crossbow projectiles
Crossbow projectiles can be displayed by adding `rc-projectile-[material]` classes.
```html
<div class="rc-item rc-minecraft_crossbow rc-projectile-minecraft_tipped_arrow" style="width: 64px; height: 64px"></div>
```

#### Dynamic tinting
Icons can consist of two layers, which can be separately tinted using CSS.
This is necessary whenever colors can change dynamically based on item properties (e.g. dyed leather armor or potions).

Dynamic colors can be added by setting the `--rc-layer-1-tint` and `--rc-layer-2-tint` CSS variables.
```html
<div class="rc-item rc-minecraft_leather_helmet" style="--rc-layer-1-tint: #b02e26;width: 64px; height: 64px"></div>
```

### Using renderchest as a library
#### Installation
```bash
composer require aternos/renderchest
```

When using renderchest as a library, the `ItemLibraryGenerator` class can be used to replicate the functionality of the CLI tool.
```php
(new ItemLibraryGenerator(["path/to/assets"], "path/to/output"))
    ->setNamespaces(["minecraft"])
    ->setSize(64)
    ->setQuality(2)
    ->setFormat("webp")
    ->render();
```

It is also possible to load specific models from a resource manager instead of rendering everything.
```php
$resourceManager = new \Aternos\Renderchest\Resource\FolderResourceManager(["path/to/assets"]);
$model = $resourceManager->getModel(new \Aternos\Renderchest\Resource\ResourceLocator("minecraft", "item/stone"));

$image = $model->render(64, 64);
```
