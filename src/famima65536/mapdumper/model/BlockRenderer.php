<?php

namespace famima65536\mapdumper\model;

use Closure;
use LogicException;
use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\SnowLayer;
use pocketmine\block\Stair;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SlabType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wheat;
use pocketmine\color\Color;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\SingletonTrait;

/**
 * @method static self getInstance()
 */
final class BlockRenderer{

    use SingletonTrait;

    private OffsettedCubeSizeInfo $simpleCubeSizeInfo;

    private OffsettedCubeSizeInfo $upperHalfCubeSizeInfo;
    private OffsettedCubeSizeInfo $downerHalfCubeSizeInfo;

    /**
     * @var array<int, Closure(RenderingEngine, Vector3, Cube::FACE_*, Block, array<value-of<Type::ARRAY_CONST>, Block>):void>
     */
    private array $blockRenderingFuncs = [];

    private function __construct(){
        $this->prepareCubeSizeInfo();
        
        $this->register(VanillaBlocks::GRASS(), function(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $grass, array $sides){
            $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo(Vector3::zero(), new Vector3(1, 7/8, 1)), Color::fromRGB(0xd68655), Cube::FACE_XZ & $faceToRender);
            $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo(new Vector3(0, 7/8, 0), new Vector3(1, 1/8, 1)), Color::fromRGB(0x569131), Cube::FACE_YXZ & $faceToRender);
        });
        $this->registerSimple(VanillaBlocks::DIRT(), Color::fromRGB(0xd68655));
        $this->registerSimple(VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE()), Color::fromRGB(0x9e5a31));
        $this->registerSimple(VanillaBlocks::DIRT()->setDirtType(DirtType::ROOTED()), Color::fromRGB(0x9e5a31));
        $this->register(VanillaBlocks::FARMLAND(), function(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $farmland, array $sides){
            $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo(Vector3::zero(), new Vector3(1, 7/8, 1)), Color::fromRGB(0xd68655), Cube::FACE_XZ & $faceToRender);
            $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo(new Vector3(0, 7/8, 0), new Vector3(1, 1/8, 1)), Color::fromRGB(0x915631), Cube::FACE_YXZ & $faceToRender);
        });
        $this->registerSimple(VanillaBlocks::OAK_LOG(), Color::fromRGB(0xa96e35));
        $this->registerSimple(VanillaBlocks::SPRUCE_LOG(), Color::fromRGB(0x422f03));
        $this->registerSimple(VanillaBlocks::BIRCH_LOG(), Color::fromRGB(0xe5f0e5));

        $this->registerSimple(VanillaBlocks::OAK_LEAVES(), Color::fromRGB(0x3e823a));
        $this->registerSimple(VanillaBlocks::BIRCH_LEAVES(), Color::fromRGB(0x91c196));

        $oakWoodColor = Color::fromRGB(0xceb271);
        $this->registerSimple(VanillaBlocks::OAK_WOOD(), $oakWoodColor);
        $this->registerHalf(VanillaBlocks::OAK_SLAB(), $oakWoodColor);
        $this->registerSimple(VanillaBlocks::OAK_STAIRS(), $oakWoodColor);
        
        $this->registerSimple(VanillaBlocks::GLASS(), Color::fromRGBA(0xf0f0f030));


        $sandColor = Color::fromRGB(0xedefaa);
        $this->registerSimple(VanillaBlocks::SAND(), $sandColor);
        $this->registerSimple(VanillaBlocks::SANDSTONE(), $sandColor);
        $this->registerHalf(VanillaBlocks::SANDSTONE_SLAB(), $sandColor);
        $this->registerSimple(VanillaBlocks::SANDSTONE_WALL(), $sandColor);
        $this->registerSimple(VanillaBlocks::SANDSTONE_STAIRS(), $sandColor);

        $stoneColor = new Color(192, 192, 192);
        $this->registerSimple(VanillaBlocks::STONE(), $stoneColor);
        $this->registerHalf(VanillaBlocks::STONE_SLAB(), $stoneColor);
        $this->registerSimple(VanillaBlocks::STONE_STAIRS(), $stoneColor);
        $this->registerSimple(VanillaBlocks::SMOOTH_STONE(), $stoneColor);

        $deepslateColor = Color::fromRGB(0x3a3938);
        $this->registerSimple(VanillaBlocks::DEEPSLATE(), $deepslateColor);
        $this->registerHalf(VanillaBlocks::DEEPSLATE_TILE_SLAB(), $deepslateColor);
        $this->registerSimple(VanillaBlocks::DEEPSLATE_BRICKS(), $deepslateColor);
        $this->registerSimple(VanillaBlocks::DEEPSLATE_BRICK_STAIRS(), $deepslateColor);
        $this->registerSimple(VanillaBlocks::COBBLED_DEEPSLATE(), $deepslateColor);
        $this->registerHalf(VanillaBlocks::COBBLED_DEEPSLATE_SLAB(), $deepslateColor);
        $this->registerSimple(VanillaBlocks::COBBLED_DEEPSLATE_STAIRS(), $deepslateColor);

        $this->registerSimple(VanillaBlocks::BEDROCK(), Color::fromRGB(0x474544));
        
        $this->registerSimple(VanillaBlocks::WOOL()->setColor(DyeColor::BROWN()), Color::fromRGB(0x754303));

        $polishedDioriteColor = Color::fromRGB(0xc4c4c4);
        $this->registerSimple(VanillaBlocks::POLISHED_DIORITE(), $polishedDioriteColor);
        $this->registerHalf(VanillaBlocks::POLISHED_DIORITE_SLAB(), $polishedDioriteColor);
        $this->registerSimple(VanillaBlocks::POLISHED_DIORITE_STAIRS(), $polishedDioriteColor);
        $polishedAndesiteColor = Color::fromRGB(0x7f7f7f);
        $this->registerSimple(VanillaBlocks::POLISHED_ANDESITE(), $polishedAndesiteColor);
        $this->registerHalf(VanillaBlocks::POLISHED_ANDESITE_SLAB(), $polishedAndesiteColor);
        $this->registerSimple(VanillaBlocks::POLISHED_ANDESITE_STAIRS(), $polishedAndesiteColor);
        $polishedGraniteColor = Color::fromRGB(0xf98c4d);
        $this->registerSimple(VanillaBlocks::POLISHED_GRANITE(), $polishedGraniteColor);
        $this->registerHalf(VanillaBlocks::POLISHED_GRANITE_SLAB(), $polishedGraniteColor);
        $this->registerSimple(VanillaBlocks::POLISHED_GRANITE_STAIRS(), $polishedGraniteColor);
        $cobblestoneColor = Color::fromRGB(0x707070);
        $this->registerSimple(VanillaBlocks::COBBLESTONE(), $cobblestoneColor);
        $this->registerHalf(VanillaBlocks::COBBLESTONE_SLAB(), $cobblestoneColor);
        $this->registerSimple(VanillaBlocks::COBBLESTONE_STAIRS(), $cobblestoneColor);
        $dioriteColor = Color::fromRGB(0xc2c6c0);
        $this->registerSimple(VanillaBlocks::DIORITE(), $dioriteColor);
        $this->registerHalf(VanillaBlocks::DIORITE_SLAB(), $dioriteColor);
        $this->registerSimple(VanillaBlocks::DIORITE_STAIRS(), $dioriteColor);
        $andesiteColor = Color::fromRGB(0xa6aaa5);
        $this->registerSimple(VanillaBlocks::ANDESITE(), $andesiteColor);
        $this->registerHalf(VanillaBlocks::ANDESITE_SLAB(), $andesiteColor);
        $this->registerSimple(VanillaBlocks::ANDESITE_STAIRS(), $andesiteColor);
        $graniteColor = Color::fromRGB(0xad7c70);
        $this->registerSimple(VanillaBlocks::GRANITE(), $graniteColor);
        $this->registerHalf(VanillaBlocks::GRANITE_SLAB(), $graniteColor);
        $this->registerSimple(VanillaBlocks::GRANITE_STAIRS(), $graniteColor);

        $this->registerSimple(VanillaBlocks::ICE(),  Color::fromRGBA(0xcde6f4bb));
        $this->registerSimple(VanillaBlocks::SNOW(), Color::fromRGB(0xffffff));
        $this->registerSimple(VanillaBlocks::EMERALD(), Color::fromRGB(0x90f4ab));
        $this->registerSimple(VanillaBlocks::DIAMOND(), Color::fromRGB(0x90e7f4));
        $this->registerSimple(VanillaBlocks::REDSTONE(), Color::fromRGB(0xdd5b4f));
        $this->registerSimple(VanillaBlocks::GOLD(), Color::fromRGB(0xefe070));
        $this->registerSimple(VanillaBlocks::MONSTER_SPAWNER(), Color::fromRGBA(0x42424199));
        $quartzColor = Color::fromRGB(0xf0f0f0);
        $this->registerSimple(VanillaBlocks::QUARTZ_BRICKS(), $quartzColor);
        $this->registerSimple(VanillaBlocks::QUARTZ(), $quartzColor);
        $this->registerSimple(VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::PINK()), Color::fromRGB(0xba4e44));
        $this->registerSimple(VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::CYAN()), Color::fromRGB(0x4c5456));
        $this->registerSimple(VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::YELLOW()), Color::fromRGB(0xf7c72a));
        $this->registerSimple(VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::LIME()), Color::fromRGB(0x667f13));

        $waterColor = Color::fromRGB(0x85c8e2);
        $this->registerSimple(VanillaBlocks::WATER(), $waterColor);
        $this->registerSimple(VanillaBlocks::WATER_CAULDRON(), $waterColor);
        $lavaColor = Color::fromRGB(0xef7840);
        $this->registerSimple(VanillaBlocks::LAVA(), $lavaColor);
        $this->registerSimple(VanillaBlocks::LAVA_CAULDRON(), $lavaColor);

        $this->registerSimple(VanillaBlocks::NETHERRACK(), Color::fromRGB(0x84160e));
        $this->register(VanillaBlocks::SNOW_LAYER(), function(RenderingEngine $engine, Vector3 $position, int $faceToRender, SnowLayer $snowLayer, array $sides){
            $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo(Vector3::zero(), new Vector3(1, $snowLayer->getLayers()/SnowLayer::MAX_LAYERS, 1)), Color::fromRGB(0xf0f0f0), ($faceToRender & Cube::FACE_XZ) | Cube::FACE_Y);
        });

        $this->register(VanillaBlocks::WHEAT(), function(RenderingEngine $engine, Vector3 $position, int $faceToRender, Wheat $wheat, array $sides){
            $growLevel = $wheat->getAge()/Wheat::MAX_AGE;
            $color = new Color(
                (int) floor(104 * (1 - $growLevel) + 216 * $growLevel),
                (int) floor(206 * (1 - $growLevel) + 216 * $growLevel),
                (int) 47,
            );
            $cube = new Vector3(1/16, $growLevel*0.8, 1/16);
            $offsets = [
                new Vector3(3/16, 0.0, 3/16),
                new Vector3(12/16, 0.0, 3/16),
                new Vector3(3/16, 0.0, 12/16),
                new Vector3(12/16, 0.0, 12/16),
            ];
            foreach($offsets as $offset){
                $engine->putCube($position->x, $position->y, $position->z, new OffsettedCubeSizeInfo($offset, $cube), $color, Cube::FACE_YXZ);
            }
        });
    }

    private function prepareCubeSizeInfo() : void{
        $zero = Vector3::zero();
        $this->simpleCubeSizeInfo = new OffsettedCubeSizeInfo($zero, new Vector3(1, 1, 1));
        $halfBlock = new Vector3(1, 0.5, 1);
        $this->downerHalfCubeSizeInfo = new OffsettedCubeSizeInfo($zero, $halfBlock);
        $this->upperHalfCubeSizeInfo = new OffsettedCubeSizeInfo(new Vector3(0.0, 0.5, 0.0), $halfBlock);
    }

    private function getTypeIdWithData(Block $block) : int{
        return ($block->getTypeId() << Block::INTERNAL_STATE_DATA_BITS) + $block->computeTypeData();
    }

    private function registerSimple(Block $block, Color $color) : void{
        $this->register($block, function(RenderingEngine $engine, Vector3 $position, int $faceToRender) use($color){ $engine->putCube($position->x, $position->y, $position->z, $this->simpleCubeSizeInfo, $color, $faceToRender); });
    }

    private function registerHalf(Slab $block, Color $color) : void{
        $this->register($block, function(RenderingEngine $engine, Vector3 $position, int $faceToRender, Slab $slab) use($color){
            $engine->putCube($position->x, $position->y, $position->z, match($slab->getSlabType()->id()){
                SlabType::BOTTOM()->id() => $this->downerHalfCubeSizeInfo,
                SlabType::TOP()->id() => $this->upperHalfCubeSizeInfo,
                SlabType::DOUBLE()->id() => $this->simpleCubeSizeInfo,
                default => throw new LogicException("cannot happen")
            }, $color, (Cube::FACE_XZ & $faceToRender) | Cube::FACE_Y);
        });
    }

    private function registerStair(Stair $block, Color $color) : void{
        $this->register($block, function(RenderingEngine $engine, Vector3 $position, Stair $stair, array $sides) use($color){
            /** @var OffsettedCubeSizeInfo[] */
            $cubes = [];
            switch($stair->isUpsideDown()){
                case false:
                    $cubes[] = $this->downerHalfCubeSizeInfo;
                    break;

                case true:
                    $cubes[] = $this->upperHalfCubeSizeInfo;
                    break;
            }
            foreach($cubes as $cube){
                $engine->putCube($position->x, $position->y, $position->z, $cube, $color, Cube::FACE_YXZ);
            }
        });
    }

    /**
     * @template TBlock of Block
     * @phpstan-param TBlock $block
     * @phpstan-param Closure(RenderingEngine, Vector3, Cube::FACE_*, TBlock, array<value-of<Type::ARRAY_CONST>, Block>):void $renderingFunc 
     */
    private function register(Block $block, Closure $renderingFunc) : void{
        $typeWithData = $this->getTypeIdWithData($block);
        $this->blockRenderingFuncs[$typeWithData] = $renderingFunc;
    }

    public function isRendered(Block $block): bool{
        return isset($this->blockRenderingFuncs[$this->getTypeIdWithData($block)]);
    }

    /**
     * @phpstan-param array<value-of<Facing::ALL>, Block> $sides
     * @phpstan-param Cube::FACE_* $faceToRender
     */
    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides): void{
        ($this->blockRenderingFuncs[$this->getTypeIdWithData($block)])($engine, $position, $faceToRender, $block, $sides);
    }
}
