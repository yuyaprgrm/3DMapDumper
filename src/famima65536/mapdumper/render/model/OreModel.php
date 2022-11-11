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
final class OreModel implements BlockModel{

    private CubeInfo $cubeInfo;

    /**
     * @phpstan-var array<Cube::FACE_Y|Cube::FACE_X|Cube::FACE_Z>
     * @var array<int, CubeInfo[]>
     */
    private array $oreCubes;

    private const ORE_THINKNESS = 0.125;
    private const ORE_HEIGHT = 0.2;
    private const ORE_WIDTH = 0.3;
    
    public function __construct(int $baseArgbColor, int $oreArgbColor){
        $this->cubeInfo = new CubeInfo(Vector3::zero(), new Vector3(1, 1, 1), $baseArgbColor, null);
        $this->oreCubes = [];

        $this->oreCubes[Cube::FACE_Y] = [
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.6, 0.1), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.5, 0.2), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.1, 0.5), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.6, 0.1), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.5, 0.2), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.1, 0.5), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
        ];


        $this->oreCubes[Cube::FACE_X] = [
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.6, 0.1), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.5, 0.2), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(-self::ORE_THINKNESS, 0.1, 0.5), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.6, 0.1), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.5, 0.2), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
            new CubeInfo(new Vector3(1, 0.1, 0.5), new Vector3(self::ORE_THINKNESS, self::ORE_HEIGHT, self::ORE_WIDTH), $oreArgbColor, null),
        ];
        
        $this->oreCubes[Cube::FACE_Z] = [
            new CubeInfo(new Vector3(0.1, 0.6, -self::ORE_THINKNESS), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
            new CubeInfo(new Vector3(0.2, 0.5, -self::ORE_THINKNESS), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
            new CubeInfo(new Vector3(0.8, 0.1, -self::ORE_THINKNESS), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
            new CubeInfo(new Vector3(0.1, 0.6, 1), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
            new CubeInfo(new Vector3(0.2, 0.5, 1), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
            new CubeInfo(new Vector3(0.5, 0.1, 1), new Vector3(self::ORE_WIDTH, self::ORE_HEIGHT, self::ORE_THINKNESS), $oreArgbColor, null),
        ];
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        $engine->putCube($position->x, $position->y, $position->z, $this->cubeInfo, $faceToRender);
        foreach([Cube::FACE_X, Cube::FACE_Y, Cube::FACE_Z] as $face){
            if($faceToRender & $face === $face){
                foreach($this->oreCubes[$face] as $cube){
                    $engine->putCube($position->x, $position->y, $position->z, $cube, Cube::FACE_YXZ);
                }
            }
        }
    }

    public function isCompatibleWith(Block $block): bool{
        return true;
    }
}