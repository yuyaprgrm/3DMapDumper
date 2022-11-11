<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\Cube;
use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\Torch;

/**
 * @implements BlockModel<Torch>
 */
final class TorchModel implements BlockModel{

    private CubeInfo $base;
    private CubeInfo $head;
    
    public function __construct(int $baseArgbColor, int $headArgbColor){
        $this->base = new CubeInfo(new Vector3(7/16, 0, 7/16), new Vector3(1/8, 1/2, 1/8), $baseArgbColor);
        $this->head = new CubeInfo(new Vector3(7/16, 1/2, 7/16), new Vector3(1/8, 1/8, 1/8), $headArgbColor);
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        $engine->putCube($position->x, $position->y, $position->z, $this->base, Cube::FACE_YXZ);
        $engine->putCube($position->x, $position->y, $position->z, $this->head, Cube::FACE_YXZ);
    }

    public function isCompatibleWith(Block $block): bool{
        return $block instanceof Torch;
    }
}