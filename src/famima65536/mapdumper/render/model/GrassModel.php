<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\Cube;
use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

/**
 * @implements BlockModel<Block>
 */
final class GrassModel implements BlockModel{

    private CubeInfo $base;
    private CubeInfo $top;
    
    public function __construct(int $baseArgbColor, int $topArgbColor){
        $this->base = new CubeInfo(Vector3::zero(), new Vector3(1, 7/8, 1), $baseArgbColor, null);
        $this->top = new CubeInfo(new Vector3(0, 7/8, 0), new Vector3(1, 1/8, 1), $topArgbColor, null);
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        $engine->putCube($position->x, $position->y, $position->z, $this->base, $faceToRender & Cube::FACE_XZ);
        $engine->putCube($position->x, $position->y, $position->z, $this->top, $faceToRender);
    }

    public function isCompatibleWith(Block $block): bool{
        return true;
    }

    public function isFullBox(): bool{
        return true;
    }
}