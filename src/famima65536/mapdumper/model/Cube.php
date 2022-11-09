<?php

namespace famima65536\mapdumper\model;

use pocketmine\color\Color;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\math\Axis;

final class Cube{
    public function __construct(
        public readonly Vector3 $position,
        public readonly Vector3 $size,
        public readonly Color $color,
        public readonly int $faceToRender
    ){
    }

    /**
     * @param Axis::* $axis
     * @return 
     */
    public function shouldRender(int $axis) : bool{
        return (($this->faceToRender >> $axis) & 1) === 1;
    }
}