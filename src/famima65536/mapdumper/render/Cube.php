<?php

namespace famima65536\mapdumper\render;

use pocketmine\math\Vector3;
use pocketmine\math\Axis;

final class Cube{

    
    public const FACE_Y = 1 << Axis::Y;
    public const FACE_X = 1 << Axis::X;
    public const FACE_Z = 1 << Axis::Z;
    public const FACE_YX = self::FACE_Y + self::FACE_X;
    public const FACE_YZ = self::FACE_Y + self::FACE_Z;
    public const FACE_XZ = self::FACE_X + self::FACE_Z;
    public const FACE_YXZ = self::FACE_Y + self::FACE_X + self::FACE_Z;


    public function __construct(
        public readonly Vector3 $position,
        public readonly Vector3 $size,
        public readonly int $argbColor,
        public readonly int $edgeArgbColor,
        public readonly int $faceToRender
    ){
    }

    /**
     * @phpstan-param self::FACE_Y|self::FACE_X|self::FACE_Z $face
     */
    public function shouldRender(int $face) : bool{
        return ($this->faceToRender & $face) === $face;
    }
}