<?php

namespace famima65536\mapdumper\task;

use famima65536\mapdumper\model\ChunkRenderer;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;

class MapDumpTask extends AsyncTask{

    private string $serializedChunks;
    /**
     * @var int[]
     * @phpstan-var array{int, int}
     */
    private array $startTime;

    /**
     * @param array<int, Chunk> $chunks
     */
    public function __construct(CommandSender $sender, array $chunks, private string $path, private float $yaw, private float $pitch, private float $dotsPerBlock){
        $this->startTime = hrtime();
        $this->storeLocal("sender", $sender);
        $this->serializedChunks = serialize(array_map(fn(Chunk $chunk) : string => FastChunkSerializer::serializeTerrain($chunk), $chunks));
    }

    public function onRun(): void{
        /** @var array<int, string> */
        $chunks = unserialize($this->serializedChunks);
        $this->serializedChunks = "";
        $renderer = new ChunkRenderer($this->yaw / 180 * M_PI, $this->pitch / 180 * M_PI, (new Vector3(2, 4, 3))->normalize(), $this->dotsPerBlock, $this->path, array_map(fn(string $chunk) : Chunk => FastChunkSerializer::deserializeTerrain($chunk), $chunks));
        $chunks = [];
        $renderer->render();
        $renderer->writeToFile();
    }

    public function onCompletion(): void{
        /** @var CommandSender $sender */
        $endTime = hrtime();
        $renderingTime = round(($endTime[0] - $this->startTime[0]) + ($endTime[1] - $this->startTime[1]) / 10**9, 3);
        $sender = $this->fetchLocal("sender");
        $sender->sendMessage("completed in $renderingTime s");

    }
}