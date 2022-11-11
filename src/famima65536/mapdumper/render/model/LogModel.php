<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\Cube;
use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\math\Vector3;


final class LogModel implements BlockModel{

    private CubeInfo $innerCube;
    private CubeInfo $outerCube;

    public function __construct(int $outerArgbColor, int $innerArgbColor){
        $this->innerCube = new CubeInfo(Vector3::zero(), new Vector3(1, 1, 1), $innerArgbColor, $outerArgbColor);
        $this->outerCube = new CubeInfo(Vector3::zero(), new Vector3(1, 1, 1), $outerArgbColor, null);
    }
    
    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        assert($block instanceof Wood);
        $innerFace = 1 << $block->getAxis();
        $outerFace = $innerFace ^ Cube::FACE_YXZ;
        $engine->putCube($position->x, $position->y, $position->z, $this->innerCube, $innerFace & $faceToRender);
        $engine->putCube($position->x, $position->y, $position->z, $this->outerCube, $outerFace & $faceToRender);
    }

    public function isCompatibleWith(Block $block) : bool{
        return $block instanceof Wood;
    }
}