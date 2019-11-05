<?php

namespace Bavfalcon9\MultiVersion;

/* Commands */
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\Server;
use Bavfalcon9\MultiVersion\EventManager;

class Main extends PluginBase {
    public $EventManager;

    public function onEnable() {
        $this->EventManager = new EventManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this->EventManager, $this);
    }
}