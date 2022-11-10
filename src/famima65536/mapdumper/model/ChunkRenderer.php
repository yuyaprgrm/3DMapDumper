<?php

namespace famima65536\mapdumper\model;

use GdImage;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Transparent;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\utils\Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

final class ChunkRenderer{

    private GdImage $image;

    private Trigonometric $yaw;
    private Trigonometric $pitch;

    private SignatureVector3 $viewSignature;
    private Vector3 $viewVector;
    private Vector2 $offset;

    private array $blockCache = [];

    /**
     * @param array<int, Chunk> $chunks
     */
    public function __construct(
        float $yaw,
        float $pitch,
        private Vector3 $lightDirection,
        private float $dotsPerBlock,
        private string $output,
        private array $chunks
    ){
        $this->yaw = new Trigonometric($yaw);
        $this->pitch = new Trigonometric($pitch);
        $this->viewVector = new Vector3(
            $this->yaw->cosine * $this->pitch->cosine,
            $this->pitch->sine,
            -$this->yaw->sine * $this->pitch->cosine
        );
        $this->viewSignature = new SignatureVector3(
            Signature::fromValue($this->viewVector->x),
            Signature::fromValue($this->viewVector->y),
            Signature::fromValue($this->viewVector->z),
        );
        $minX = $minY = $maxX = $maxY = 0.0;
        $this->calculateRange($minX, $minY, $maxX, $maxY);
        $this->image = Utils::assumeNotFalse(imagecreatetruecolor((int) ceil($maxX - $minX), (int) ceil($maxY - $minY)));
        $this->offset = new Vector2($minX, $minY);
    }

    private function transform(float $x, float $y, float $z) : Vector2{
        return new Vector2(
             ($x * $this->yaw->sine + $z * $this->yaw->cosine) * $this->pitch->cosine * $this->dotsPerBlock,
            -((-$x * $this->yaw->cosine + $z * $this->yaw->sine) * $this->pitch->sine  + $y * $this->pitch->cosine) * $this->dotsPerBlock
        );
    }

    private function calculateChunkLayer(int $chunkHash) : float{
        World::getXZ($chunkHash, $chunkX, $chunkZ);
        return $chunkX * $this->viewVector->x + $chunkZ * $this->viewVector->z;
    }

    private function calculateRange(float &$minX, float &$minY, float &$maxX, float &$maxY) : void{
        $minX = $minY = PHP_FLOAT_MAX;
        $maxX = $maxY = PHP_FLOAT_MIN;
        foreach($this->chunks as $hash => $chunk){
            World::getXZ($hash, $chunkX, $chunkZ);
            $x = $chunkX << SubChunk::COORD_BIT_SIZE;
            $z = $chunkZ << SubChunk::COORD_BIT_SIZE;
            foreach([
                [0, World::Y_MIN, 0],
                [SubChunk::EDGE_LENGTH, World::Y_MIN, 0],
                [0, World::Y_MIN, SubChunk::EDGE_LENGTH],
                [SubChunk::EDGE_LENGTH, World::Y_MIN, SubChunk::EDGE_LENGTH],
                [0, World::Y_MAX, 0],
                [SubChunk::EDGE_LENGTH, World::Y_MAX, 0],
                [0, World::Y_MAX, SubChunk::EDGE_LENGTH],
                [SubChunk::EDGE_LENGTH, World::Y_MAX, SubChunk::EDGE_LENGTH]
            ] as $offset){
                $v = $this->transform($x + $offset[0], $offset[1], $z + $offset[2]);
                if($v->x > $maxX) $maxX = $v->x;
                if($v->y > $maxY) $maxY = $v->y;
                if($v->x < $minX) $minX = $v->x;
                if($v->y < $minY) $minY = $v->y;
            }
        }
    }

    public function render() : void{
        uksort($this->chunks, fn(int $a, int $b) => -($this->calculateChunkLayer($a) <=> $this->calculateChunkLayer($b)));
        foreach($this->chunks as $hash => $chunk){
            $this->drawChunk($hash, $chunk);
        }
    }

    public function writeToFile() : void{
        imagepng($this->image, $this->output);
    }

    private function calculateChunkOffset(int $chunkHash) : Vector2{
        World::getXZ($chunkHash, $chunkX, $chunkZ);
        return $this->transform($chunkX << SubChunk::COORD_BIT_SIZE, 0, $chunkZ << SubChunk::COORD_BIT_SIZE);
    }

    private function drawChunk(int $chunkHash) : void{
        $factory = BlockFactory::getInstance();
        $blockRenderer = BlockRenderer::getInstance();
        $renderingEngine = new RenderingEngine($this->image, $this->yaw, $this->pitch, $this->lightDirection, $this->dotsPerBlock, $this->calculateChunkOffset($chunkHash)->subtractVector($this->offset));
        
        World::getXZ($chunkHash, $chunkX, $chunkZ);
        foreach($this->chunks[$chunkHash]->getSubChunks() as $chunkY => $subchunk){
            if(count($subchunk->getBlockLayers()) === 0){
                continue;
            }
            for($y = 0; $y < SubChunk::EDGE_LENGTH; $y++){
                $yInWorld = $y + ($chunkY << SubChunk::COORD_BIT_SIZE);
                for($x = 0; $x < SubChunk::EDGE_LENGTH; $x++){
                    $xInWorld = ($chunkX << SubChunk::COORD_BIT_SIZE) + $x;
                    for($z = 0; $z < SubChunk::EDGE_LENGTH; $z++){
                        $zInWorld = ($chunkZ << SubChunk::COORD_BIT_SIZE) + $z;
                        $block = $this->getBlock($xInWorld, $yInWorld, $zInWorld);
                        if($blockRenderer->isRendered($block)){
                            $hiderBlocks = [
                                match($this->viewSignature->y){
                                    Signature::Positive => $this->getBlock($xInWorld, $yInWorld - 1, $zInWorld),
                                    Signature::Negative => $this->getBlock($xInWorld, $yInWorld + 1, $zInWorld),
                                    Signature::Zero => null,
                                },
                                match($this->viewSignature->z){
                                    Signature::Positive => $this->getBlock($xInWorld, $yInWorld, $zInWorld - 1),
                                    Signature::Negative => $this->getBlock($xInWorld, $yInWorld, $zInWorld + 1),
                                    Signature::Zero => null,
                                },
                                match($this->viewSignature->x){
                                    Signature::Positive => $this->getBlock($xInWorld - 1, $yInWorld, $zInWorld),
                                    Signature::Negative => $this->getBlock($xInWorld + 1, $yInWorld, $zInWorld),
                                    Signature::Zero => null,
                                },
                            ];

                            $faceToRender = 0;
                            foreach($hiderBlocks as $axis => $hiderBlock){
                                if($hiderBlock === null || $hiderBlock instanceof Transparent){
                                    $faceToRender |= (1 << $axis);
                                }
                            }
                            if($faceToRender !== 0){
                                $blockRenderer->render($renderingEngine, new Vector3($x, $yInWorld, $z), $faceToRender, $block, []);
                            }
                        }
                    }
                }
            }
        }
        
        $renderingEngine->flush();
        $this->blockCache = [];
    }

    private function getBlock(int $x, int $y, int $z) : ?Block{
        $hash = World::chunkBlockHash($x, $y, $z);
        $factory = BlockFactory::getInstance();
        if(array_key_exists($hash, $this->blockCache)){
            return $this->blockCache[$hash];
        }

        $chunkHash = World::chunkHash($x >> SubChunk::COORD_BIT_SIZE, $z >> SubChunk::COORD_BIT_SIZE);
        
        if(!isset($this->chunks[$chunkHash])){
            $this->blockCache[$hash] = null;
            return null;
        }

        $fullId = $this->chunks[$chunkHash]->getFullBlock($x & SubChunk::COORD_MASK, $y, $z &  SubChunk::COORD_MASK);
        
        $block = $this->blockCache[$hash] = $factory->fromStateId($fullId);
        return $block;
    }
}