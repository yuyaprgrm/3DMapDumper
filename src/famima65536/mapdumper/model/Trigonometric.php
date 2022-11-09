<?php

namespace famima65536\mapdumper\model;

final class Trigonometric{

    public readonly float $cosine;
    public readonly float $sine;
    
    public function __construct(float $radian){
        $this->cosine = cos($radian);
        $this->sine = sin($radian);
    }
}