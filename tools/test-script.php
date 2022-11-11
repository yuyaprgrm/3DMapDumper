<?php

use famima65536\mapdumper\render\CoordinateTransformer;
use pocketmine\color\Color;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

require __DIR__."/../vendor/autoload.php";

$outdir = __DIR__."/../out/";

$P = 10;
for($p = 0; $p < $P; $p++){

    $image = imagecreatetruecolor(1000, 1000);
    $t = new CoordinateTransformer(2*M_PI*$p/$P, -M_PI/6, (new Vector3(3, 4, 5))->normalize(), 10, $image, new Vector2(500, 500));
    for($y = 0; $y < 50; $y++){
        for($x = 0; $x < 50; $x++){
            for($z = 0; $z < 50; $z++){
                if(($x + $z + $y) % 2 === 1)
                $t->putCube($x, $y, $z, new Color(mt_rand(128, 255), mt_rand(128, 255), mt_rand(128, 255)));
            }
        }
    }
    $t->flush();
    imagepng($image, $outdir.$p.".png");
}

$image = imagecreatetruecolor(1000, 1000);
$t = new CoordinateTransformer(-M_PI/6, -M_PI/4, (new Vector3(3, 4, 5))->normalize(), 40, $image, new Vector2(100, 200));
// for($y = 0; $y < 10; $y++){
//     for($x = 0; $x < 10; $x++){
//         for($z = 0; $z < 10; $z++){
//             if($x + $z + $y === 4)
//         }
//     }
// }
imagepng($image, $outdir."00.png");