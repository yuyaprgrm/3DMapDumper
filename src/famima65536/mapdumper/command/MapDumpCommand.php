<?php

namespace famima65536\mapdumper\command;

use famima65536\mapdumper\task\MapDumpTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\format\SubChunk;
use pocketmine\world\World;

class MapDumpCommand extends Command{
    

    public function __construct(
        private string $outDir
    ){
        parent::__construct("mapdump", "dump map command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) < 8){
            $sender->sendMessage("/$commandLabel <world> <yaw> <pitch> <chunkX> <chunkZ> <lengthX> <lengthZ> <dotsPerBlock>");
            return;
        }

        $world = $sender->getServer()->getWorldManager()->loadWorld($args[0]);
        $world = $sender->getServer()->getWorldManager()->getWorldByName($args[0]);
        if($world === null){
            $sender->sendMessage("invalid world name");
            return;
        }

        $chunkX = (int) $args[3];
        $chunkZ = (int) $args[4];
        $lengthX = (int) $args[5];
        $lengthZ = (int) $args[6];
        $dotsPerBlock = (float) $args[7];
        $chunks = [];
        for($x = 0; $x < $lengthX; $x++){
            for($z = 0; $z < $lengthZ; $z++){
                $chunk = $world->loadChunk($chunkX + $x, $chunkZ + $z);
                if($chunk === null){
                    // $sender->sendMessage("chunk is not generated.");
                    continue;
                }
                $chunks[World::chunkHash($x, $z)] = $chunk;
            }
        }
        $sender->sendMessage("try to dump total " . count($chunks) . " chunks");
        $sender->getServer()->getAsyncPool()->submitTask(new MapDumpTask($sender, $chunks, $this->outDir."/". ($args[0] . "@" . $chunkX . "+".$lengthX . "_" . $chunkZ. "+".$lengthZ) .".png", (float) $args[1], (float) $args[2], $dotsPerBlock));
    }
}