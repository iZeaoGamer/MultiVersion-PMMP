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

use Bavfalcon9\MultiVersion\Main;
use Bavfalcon9\MultiVersion\Utils\PacketManager;
use Bavfalcon9\MultiVersion\Utils\ProtocolVersion;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\MainLogger;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

class EventManager implements Listener {
    private $plugin;
    private $packetManager;

    public function __construct(Main $pl) {
        $this->plugin = $pl;
        $this->packetManager = new PacketManager($pl);

        // 1.12 support.
        $newVersion = new ProtocolVersion(361, '1.12.0', false);
        $newVersion = $this->packetManager->registerProtocol($newVersion);
        if (!$newVersion) MainLogger::getLogger()->critical("[MULTIVERSION]: Failed to add version: 1.12.x");
    }

    public function onRecieve(DataPacketReceiveEvent $event) {
        $this->packetManager->handlePacketRecieve($event);
        return;
    }

    public function onSend(DataPacketSendEvent $event) {
        $this->packetManager->handlePacketSent($event);
        return;
    }

}