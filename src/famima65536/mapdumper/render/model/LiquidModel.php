<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\Liquid;

/**
 * @implements BlockModel<Liquid>
 */
final class LiquidModel implements BlockModel{

    private array $cubes = [];
    
    public function __construct(int $argbColor, ?int $edgeArgbColor){
        for($i = 0; $i <= Liquid::MAX_DECAY; $i++){
            $this->cubes[$i] = new CubeInfo(Vector3::zero(), new Vector3(1, 1 - $i/Liquid::MAX_DECAY, 1), $argbColor, $edgeArgbColor);
        }
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        assert($block instanceof Liquid);
        $cube = $block->isStill() ? $this->cubes[Liquid::MAX_DECAY] : $this->cubes[$block->getDecay()];
        $engine->putCube($position->x, $position->y, $position->z, $cube, $faceToRender);
    }

    /**
     * @phpstan-assert Liquid $block
     */
    public function isCompatibleWith(Block $block): bool{
        return $block instanceof Liquid;
    }

    public function isFullBox(): bool{
        return false;
    }
}