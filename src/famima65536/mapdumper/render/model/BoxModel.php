<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

/**
 * @implements BlockModel<Block>
 */
final class BoxModel implements BlockModel{

    private CubeInfo $cubeInfo;
    
    public function __construct(int $argbColor, ?int $edgeArgbColor){
        $this->cubeInfo = new CubeInfo(Vector3::zero(), new Vector3(1, 1, 1), $argbColor, $edgeArgbColor);
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        $engine->putCube($position->x, $position->y, $position->z, $this->cubeInfo, $faceToRender);
    }

    public function isCompatibleWith(Block $block): bool{
        return true;
    }
}