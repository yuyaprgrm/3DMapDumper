<?php

namespace famima65536\mapdumper\render;

use famima65536\mapdumper\render\model\BlockModel;

final class BlockModelEntry{
    public function __construct(
        public readonly string $blockName,
        public readonly BlockModel $model
    ){
    }
}