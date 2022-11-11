<?php

namespace famima65536\mapdumper\render\model;

use famima65536\mapdumper\render\CubeInfo;
use famima65536\mapdumper\render\RenderingEngine;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\SnowLayer;

/**
 * @implements BlockModel<SnowLayer>
 */
final class SnowLayerModel implements BlockModel{

    private array $cubes = [];
    
    public function __construct(int $argbColor, ?int $edgeArgbColor){
        for($i = SnowLayer::MIN_LAYERS; $i <= SnowLayer::MAX_LAYERS; $i++){
            $this->cubes[$i - SnowLayer::MIN_LAYERS] = new CubeInfo(Vector3::zero(), new Vector3(1, $i/SnowLayer::MAX_LAYERS, 1), $argbColor, $edgeArgbColor);
        }
    }

    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void{
        assert($block instanceof SnowLayer);
        $engine->putCube($position->x, $position->y, $position->z, $this->cubes[$block->getLayers() - SnowLayer::MIN_LAYERS], $faceToRender);
    }

    /**
     * @phpstan-assert SnowLayer $block
     */
    public function isCompatibleWith(Block $block): bool{
        return $block instanceof SnowLayer;
    }
}