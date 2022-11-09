<?php

namespace famima65536\mapdumper\model;

use pocketmine\math\Vector3;

final class OffsettedCubeSizeInfo{
    public function __construct(
        public readonly Vector3 $offset,
        public readonly Vector3 $size
    ){
    }
}