<?php

/**
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

declare(strict_types=1);

namespace Bavfalcon9\MultiVersion;

use Bavfalcon9\MultiVersion\Utils\PacketManager;
use Bavfalcon9\MultiVersion\Utils\ProtocolVersion;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

class EventManager implements Listener {
    private $plugin;
    private $packetManager;

    public function __construct(Main $pl) {
        $this->plugin = $pl;
        $this->packetManager = new PacketManager($pl);
        $this->loadMultiVersion();
    }

    public function onReceive(DataPacketReceiveEvent $event) {
        $this->packetManager->handlePacketReceive($event);
        return;
    }

    public function onSend(DataPacketSendEvent $event) {
        $this->packetManager->handlePacketSent($event);
        return;
    }

    private function loadMultiVersion() {
        if ($this->plugin->server_version === '1.12.0') {
            // 1.13 support on MCPE 1.12
            $newVersion = new ProtocolVersion(ProtocolVersion::VERSIONS['1.13.0'], '1.13.0', false);
            $newVersion->setProtocolPackets([
                "LoginPacket" => 0x01,
                "StartGamePacket" => 0x0b,
                "PlayerListPacket" => 0x3f,
                "PlayerSkinPacket" => 0x5d,
                "UpdateBlockPacket" => 0x15
            ]);
            $newVersion->setListeners([
                'UpdateBlockMaps'
            ]);
            $newVersion = $this->packetManager->registerProtocol($newVersion);
            define('MULTIVERSION_v1_13_0', $this->plugin->getDataFolder().'v1_13_0');
            if (!$newVersion) {
                MainLogger::getLogger()->critical("[MULTIVERSION]: Failed to add version: 1.13.x");
            } else {
                MainLogger::getLogger()->info("§aLoaded support for: 1.13.x");
            }
        }

        if ($this->plugin->server_version === '1.13.0') {
            // 1.12 support on MCPE 1.13
            $newVersion = new ProtocolVersion(ProtocolVersion::VERSIONS['1.12.0'], '1.12.0', false);
            $newVersion->setProtocolPackets([
                "LoginPacket" => 0x01,
                "StartGamePacket" => 0x0b,
                "RespawnPacket" => 0x2d,
                "PlayerListPacket" => 0x3f,
                "PlayerSkinPacket" => 0x5d,
                "ExplodePacket" => 0x17,
                "ResourcePackDataInfoPacket" => 0x52
            ]);
            $newVersion = $this->packetManager->registerProtocol($newVersion);
            define('MULTIVERSION_v1_12_0', $this->plugin->getDataFolder().'v1_12_0');
            if (!$newVersion) {
                MainLogger::getLogger()->critical("[MULTIVERSION]: Failed to add version: 1.12.x");
            } else {
                MainLogger::getLogger()->info("§aLoaded support for: 1.12.x");
            }
        }
    }

}