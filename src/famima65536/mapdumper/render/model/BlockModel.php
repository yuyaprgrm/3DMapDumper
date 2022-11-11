<?php

namespace famima65536\mapdumper\render\model;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use famima65536\mapdumper\render\RenderingEngine;

/**
 * @template TBlock of Block
 */
interface BlockModel{

    /**
     * @phpstan-param Cube::FACE_* $faceToRender
     * @phpstan-param TBlock $block
     * @phpstan-param array<value-of<Type::ARRAY_CONST>, Block> $sides
     * @param array<int, Block> $sides
     */
    public function render(RenderingEngine $engine, Vector3 $position, int $faceToRender, Block $block, array $sides) : void;

    public function isCompatibleWith(Block $block) : bool;

    public function isFullBox() : bool;
}