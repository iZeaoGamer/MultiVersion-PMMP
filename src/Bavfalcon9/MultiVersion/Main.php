<?php
/***
 *    ___  ___      _ _   _ _   _               _             
 *    |  \/  |     | | | (_) | | |             (_)            
 *    | .  . |_   _| | |_ _| | | | ___ _ __ ___ _  ___  _ __  
 *    | |\/| | | | | | __| | | | |/ _ \ '__/ __| |/ _ \| '_ \ 
 *    | |  | | |_| | | |_| \ \_/ /  __/ |  \__ \ | (_) | | | |
 *    \_|  |_/\__,_|_|\__|_|\___/ \___|_|  |___/_|\___/|_| |_|
 * 
 * Copyright (C) 2019 Olybear9 (Bavfalcon9)                            
 *                                                            
 */
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
        $this->saveResource('v1_12_0/block_id_map.json');
        $this->saveResource('v1_12_0/block_states.json');
        $this->saveResource('v1_12_0/entity_id_map.json');
        $this->saveResource('v1_12_0/item_id_map.json');
        $this->saveResource('v1_12_0/recipies.json');
    }

}