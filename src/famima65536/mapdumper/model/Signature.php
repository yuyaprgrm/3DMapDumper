<?php

namespace famima65536\mapdumper\model;

enum Signature: int{
    case Positive = 1;
    case Negative = -1;
    case Zero = 0;

    public static function fromValue(float $value) : self{
        return match(true){
            $value > 0.0 => self::Positive,
            $value < 0.0 => self::Negative,
            default => self::Zero
        };
    }

}