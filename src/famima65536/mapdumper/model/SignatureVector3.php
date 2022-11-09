<?php

namespace famima65536\mapdumper\model;

final class SignatureVector3{
    public Signature $x;
    public Signature $y;
    public Signature $z;

    public function __construct(Signature $x, Signature $y, Signature $z){
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}