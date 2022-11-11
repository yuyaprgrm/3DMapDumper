<?php

namespace famima65536\mapdumper\render;

use famima65536\mapdumper\render\model\BoxModel;
use famima65536\mapdumper\render\model\GrassModel;
use famima65536\mapdumper\render\model\LiquidModel;
use famima65536\mapdumper\render\model\LogModel;
use famima65536\mapdumper\render\model\OreModel;
use famima65536\mapdumper\render\model\SlabModel;
use famima65536\mapdumper\render\model\SnowLayerModel;
use famima65536\mapdumper\render\model\TorchModel;
use InvalidArgumentException;

final class BlockModelParser{
    private array $json;
    public function __construct(private string $path){
        $this->json = json_decode(file_get_contents($path), true);
    }

    /**
     * @return BlockModelEntry[]
     */
    public function parse() : array{
        $version = $this->json["version"];
        return match($version){
            0 => $this->parseV0(),
            default => new InvalidArgumentException("unknown version")
        };
    }

    /**
     * @return BlockModelEntry[]
     */
    private function parseV0() : array{
        $entries = $this->json["entries"];
        return array_map(fn(array $entry) : BlockModelEntry => new BlockModelEntry(
            $entry["name"], match($entry["model"]){
                "box" => self::parseBox($entry),
                "snowlayer" => self::parseSnowLayer($entry),
                "slab" => self::parseSlab($entry),
                "ore" => self::parseOre($entry),
                "grass" => self::parseGrass($entry),
                "log" => self::parseLog($entry),
                "torch" => self::parseTorch($entry),
                "liquid" => self::parseLiquid($entry)
            }, $entry["strict_state"] ?? false
        ), $entries);
    }

    private static function parseBox(array $json) : BoxModel{
        return new BoxModel(
            self::parseColor($json["color"]),
            self::parseColorOptional($json["edge_color"] ?? null)
        );
    }

    private static function parseSnowLayer(array $json) : SnowLayerModel{
        return new SnowLayerModel(
            self::parseColor($json["color"]),
            self::parseColorOptional($json["edge_color"] ?? null)
        );
    }

    private static function parseSlab(array $json) : SlabModel{
        return new SlabModel(
            self::parseColor($json["color"]),
            self::parseColorOptional($json["edge_color"] ?? null)
        );
    }

    private static function parseOre(array $json) : OreModel{
        return new OreModel(self::parseColor($json["color"]), self::parseColor($json["ore_color"]));
    }

    private static function parseGrass(array $json) : GrassModel{
        return new GrassModel(self::parseColor($json["color"]), self::parseColor($json["top_color"]));
    }

    private static function parseLog(array $json) : LogModel{
        return new LogModel(self::parseColor($json["color"]), self::parseColor($json["inner_color"]));
    }

    private static function parseTorch(array $json) : TorchModel{
        return new TorchModel(self::parseColor($json["color"]), self::parseColor($json["head_color"]));
    }

    private static function parseLiquid(array $json) : LiquidModel{
        return new LiquidModel(
            self::parseColor($json["color"]),
            self::parseColorOptional($json["edge_color"] ?? null)
        );
    }

    private static function parseColorOptional(?array $json) : ?int{
        if($json === null) return null;
        return self::parseColor($json);
    }

    private static function parseColor(array $json) : int{
        $alpha = $json["alpha"] ?? 0xff;
        $r = $json["r"] & 0xff;
        $g = $json["g"] & 0xff;
        $b = $json["b"] & 0xff;
        return ($alpha << 24) | ($r << 16) | ($g << 8) | $b;
    }

}