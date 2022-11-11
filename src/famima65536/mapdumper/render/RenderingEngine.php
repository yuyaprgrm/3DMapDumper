<?php

namespace famima65536\mapdumper\render;

use GdImage;
use pocketmine\color\Color;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\utils\Utils;

final class RenderingEngine{

    private SignatureVector3 $viewSignature;
    private Vector3 $viewVector;

    /**
     * @var Cube[]
     */
    private array $cubes = [];

    /**
     * @var int[]
     */
    private array $palette = [];

    public function __construct(
        private GdImage $image,
        private Trigonometric $yaw,
        private Trigonometric $pitch,
        private Vector3 $lightDirection,
        private float $dotsPerBlock,
        private Vector2 $offset
    ){
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

    }

    private function transform(float $x, float $y, float $z) : Vector2{
        return new Vector2(
             ($x * $this->yaw->sine + $z * $this->yaw->cosine) * $this->pitch->cosine * $this->dotsPerBlock,
            -((-$x * $this->yaw->cosine + $z * $this->yaw->sine) * $this->pitch->sine  + $y * $this->pitch->cosine) * $this->dotsPerBlock
        );
    }

    /**
     * 
     */
    public function putCube(float $x, float $y, float $z, CubeInfo $cubeInfo, int $faceToRender) : void{
        $this->cubes[] = new Cube($cubeInfo->offset->add($x, $y, $z), $cubeInfo->size, $cubeInfo->argbColor, $cubeInfo->edgeArgbColor, $faceToRender);
    }

    private function calculateCubeLayer(Cube $cube) : float{
        return $cube->position->x * $this->viewVector->x + $cube->position->y * $this->viewVector->y + $cube->position->z * $this->viewVector->z;
    }

    public function flush() : void{
        usort($this->cubes, fn(Cube $a, Cube $b) => -($this->calculateCubeLayer($a) <=> $this->calculateCubeLayer($b)));
        foreach($this->cubes as $cube){
            $this->drawCube($cube);
        }
        $this->cubes = [];
    }

    private function drawCube(Cube $cube) : void{
        $color = Color::fromARGB($cube->argbColor);
        $a = $this->transform($cube->position->x, $cube->position->y, $cube->position->z);
        $b = $this->transform($cube->position->x+$cube->size->x, $cube->position->y, $cube->position->z);
        $c = $this->transform($cube->position->x, $cube->position->y, $cube->position->z+$cube->size->z);
        $d = $this->transform($cube->position->x+$cube->size->x, $cube->position->y, $cube->position->z+$cube->size->z);
        $e = $this->transform($cube->position->x, $cube->position->y+$cube->size->y, $cube->position->z);
        $f = $this->transform($cube->position->x+$cube->size->x, $cube->position->y+$cube->size->y, $cube->position->z);
        $g = $this->transform($cube->position->x, $cube->position->y+$cube->size->y, $cube->position->z+$cube->size->z);
        $h = $this->transform($cube->position->x+$cube->size->x, $cube->position->y+$cube->size->y, $cube->position->z+$cube->size->z);
        
        $xLevel = $this->lightDirection->dot(new Vector3(1, 0, 0));
        $yLevel = $this->lightDirection->dot(new Vector3(0, 1, 0));
        $zLevel = $this->lightDirection->dot(new Vector3(0, 0, 1));
        if($xLevel < 0) $xLevel = 0;
        if($yLevel < 0) $yLevel = 0;
        if($zLevel < 0) $zLevel = 0;

        $hasEdgeColor = $cube->edgeArgbColor !== null;
        if($hasEdgeColor){
            $edgeColor = Color::fromARGB($cube->edgeArgbColor);
            $xEdgeColor = new Color((int) ($edgeColor->getR() * $xLevel), (int) ($edgeColor->getG() * $xLevel), (int) ($edgeColor->getB() * $xLevel), $edgeColor->getA());
            $yEdgeColor = new Color((int) ($edgeColor->getR() * $yLevel), (int) ($edgeColor->getG() * $yLevel), (int) ($edgeColor->getB() * $yLevel), $edgeColor->getA());
            $zEdgeColor = new Color((int) ($edgeColor->getR() * $zLevel), (int) ($edgeColor->getG() * $zLevel), (int) ($edgeColor->getB() * $zLevel), $edgeColor->getA());
        }
        $xColor = new Color((int) ($color->getR() * $xLevel), (int) ($color->getG() * $xLevel), (int) ($color->getB() * $xLevel), $color->getA());
        $yColor = new Color((int) ($color->getR() * $yLevel), (int) ($color->getG() * $yLevel), (int) ($color->getB() * $yLevel), $color->getA());
        $zColor = new Color((int) ($color->getR() * $zLevel), (int) ($color->getG() * $zLevel), (int) ($color->getB() * $zLevel), $color->getA());

        if($this->viewSignature->x !== Signature::Zero && $cube->shouldRender(Cube::FACE_X)){
            $gdColor = $this->getGdColorByColor($xColor);
            $points = match($this->viewSignature->x){
                Signature::Positive => [
                    (int) ($a->x+$this->offset->x), (int) ($a->y+$this->offset->y),
                    (int) ($c->x+$this->offset->x), (int) ($c->y+$this->offset->y),
                    (int) ($g->x+$this->offset->x), (int) ($g->y+$this->offset->y),
                    (int) ($e->x+$this->offset->x), (int) ($e->y+$this->offset->y),
                ],
                Signature::Negative => [
                    (int) ($b->x+$this->offset->x), (int) ($b->y+$this->offset->y),
                    (int) ($d->x+$this->offset->x), (int) ($d->y+$this->offset->y),
                    (int) ($h->x+$this->offset->x), (int) ($h->y+$this->offset->y),
                    (int) ($f->x+$this->offset->x), (int) ($f->y+$this->offset->y),
                ]
            };
            imagefilledpolygon($this->image,
                $points,
                $gdColor
            );
            if ($hasEdgeColor) {
                imagepolygon(
                    $this->image,
                    $points,
                    $this->getGdColorByColor($xEdgeColor)
                );
            }
        }

        if($this->viewSignature->z !== Signature::Zero && $cube->shouldRender(Cube::FACE_Z)){

            $gdColor = $this->getGdColorByColor($zColor);
            $points = match($this->viewSignature->z){
                Signature::Positive => [
                    (int) ($a->x+$this->offset->x), (int) ($a->y+$this->offset->y),
                    (int) ($b->x+$this->offset->x), (int) ($b->y+$this->offset->y),
                    (int) ($f->x+$this->offset->x), (int) ($f->y+$this->offset->y),
                    (int) ($e->x+$this->offset->x), (int) ($e->y+$this->offset->y),
                ],
                Signature::Negative => [
                    (int) ($c->x+$this->offset->x), (int) ($c->y+$this->offset->y),
                    (int) ($d->x+$this->offset->x), (int) ($d->y+$this->offset->y),
                    (int) ($h->x+$this->offset->x), (int) ($h->y+$this->offset->y),
                    (int) ($g->x+$this->offset->x), (int) ($g->y+$this->offset->y),
                ],
            };
            imagefilledpolygon($this->image,
                $points,
                $gdColor
            );
            if ($hasEdgeColor) {
                imagepolygon(
                    $this->image,
                    $points,
                    $this->getGdColorByColor($zEdgeColor)
                );
            }
        }

        if($this->viewSignature->y !== Signature::Zero && $cube->shouldRender(Cube::FACE_Y)){
            
            $gdColor = $this->getGdColorByColor($yColor);
            $points = match($this->viewSignature->y){
                Signature::Positive => [
                    (int) ($a->x+$this->offset->x), (int) ($a->y+$this->offset->y),
                    (int) ($b->x+$this->offset->x), (int) ($b->y+$this->offset->y),
                    (int) ($d->x+$this->offset->x), (int) ($d->y+$this->offset->y),
                    (int) ($c->x+$this->offset->x), (int) ($c->y+$this->offset->y),
                ],
                Signature::Negative => [
                    (int) ($e->x+$this->offset->x), (int) ($e->y+$this->offset->y),
                    (int) ($f->x+$this->offset->x), (int) ($f->y+$this->offset->y),
                    (int) ($h->x+$this->offset->x), (int) ($h->y+$this->offset->y),
                    (int) ($g->x+$this->offset->x), (int) ($g->y+$this->offset->y),
                ],
            };
            imagefilledpolygon($this->image,
                $points,
                $gdColor
            );
            if ($hasEdgeColor) {
                imagepolygon(
                    $this->image,
                    $points,
                    $this->getGdColorByColor($yEdgeColor)
                );
            }
        }

    }

    private function getGdColorByColor(Color $color) : int{
        $argb = $color->toARGB();
        return $this->palette[$argb] ?? ($this->palette[$argb] = Utils::assumeNotFalse(imagecolorallocatealpha($this->image, $color->getR(), $color->getG(), $color->getB(), intdiv(255 - $color->getA(), 2))));
    }
}