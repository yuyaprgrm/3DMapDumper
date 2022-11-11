<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\Cube;
use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\utils\SlabType;
use pocketmine\math\Vector3;

/**
 * @implements BlockModel<Slab>
 */
final class SlabModel implements BlockModel{

    private CubeInfo $bottom;
    private CubeInfo $top;

    public function __construct(int $argbColor, ?int $edgeArgbColor){
        $this->bottom = new CubeInfo(Vector3::zero(), new Vector3(1, 0.5, 1), $argbColor, $edgeArgbColor);
        $this->top = new CubeInfo(new Vector3(0, 0.5, 0), new Vector3(1, 0.5, 1), $argbColor, $edgeArgbColor);
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides): void{
        assert($block instanceof Slab);
        $type = $block->getSlabType();
        switch($type->id()){
            case SlabType::BOTTOM()->id(): 
                $engine->putCube($position->x, $position->y, $position->z, $this->bottom, $faceToRender | Cube::FACE_Y);
                break;

            case SlabType::TOP()->id():
                $engine->putCube($position->x, $position->y, $position->z, $this->top, $faceToRender);
                break;

            case SlabType::DOUBLE()->id():
                $engine->putCube($position->x, $position->y, $position->z, $this->bottom, $faceToRender);
                $engine->putCube($position->x, $position->y, $position->z, $this->top, $faceToRender);
        };
    }

    /**
     * @phpstan-assert Slab $block
     */
    public function isCompatibleWith(Block $block): bool{
        return $block instanceof Slab;
    }

    public function isFullBox(): bool{
        return false;
    }
}