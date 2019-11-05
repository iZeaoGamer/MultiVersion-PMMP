<?php

namespace Bavfalcon9\MultiVersion;

use Bavfalcon9\MultiVersion\Main;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;

class EventManager implements Listener {
    private $plugin;
    
    public function __construct(Main $pl) {
        $this->plugin = $pl;
    }
}