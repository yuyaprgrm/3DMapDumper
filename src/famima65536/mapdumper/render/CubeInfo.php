<?php

namespace famima65536\mapdumper\render;

use pocketmine\color\Color;
use pocketmine\math\Vector3;

final class CubeInfo{
    public function __construct(
        public readonly Vector3 $offset,
        public readonly Vector3 $size,
        public readonly int $argbColor,
        public readonly ?int $edgeArgbColor
    ){
    }
}