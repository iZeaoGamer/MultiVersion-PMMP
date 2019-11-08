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

namespace Bavfalcon9\MultiVersion\Utils;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\utils\MainLogger;

class ProtocolVersion {
    public const DEVELOPER = true; // set to true for debug
    public const VERSIONS = [
        '1.12.0' => 361,
        '1.13.0' => 388
    ];
    private $protocol;
    private $protocolPackets = [];
    private $restricted = false;
    private $dir = '';
    private $minecraftVersion = '1.13.0';

    /**
     * @param int  $protocol
     * @param String $MCPE
     * @param bool   $restrict
     */
    public function __construct(int $protocol, String $MCPE, Bool $restrict=false) {
        $fixedMCPE = 'v'.implode('_', explode('.', $MCPE));
        $this->protocol = $protocol;
        $this->dir = "Bavfalcon9\\MultiVersion\\Protocols\\".$fixedMCPE."\\Packets\\";
        $this->restricted = $restrict;
        $this->minecraftVersion = $MCPE;
    }

    public function setProtocolPackets(Array $packets) {
        $this->protocolPackets = $packets;
    }

    public function getProtocol(): int {
        return $this->protocol;
    }

    public function getProtocolPackets(): array {
        return $this->protocolPackets;
    }

    public function getMinecraftVersion(): String {
        return $this->minecraftVersion;
    }

    public function getPacketName(Float $id): ?String {
        foreach ($this->protocolPackets as $name=>$pid) {
            if ($id == $pid) return $name;
            else continue;
        }

        return ''.$id;
    }

    public function changePacket(String &$name, &$oldPacket, String $type='Sent') {
        if (!isset($this->protocolPackets[$name]) && $this->restricted === true) return null;
        if (!isset($this->protocolPackets[$name])) {
            if (self::DEVELOPER === true) MainLogger::getLogger()->info("§c[MultiVersion] DEBUG:§e Packet §8[§f {$oldPacket->getName()} §8| §f".$oldPacket::NETWORK_ID."§8]§e requested a change but no change supported §a{$type}§e.");
            return $oldPacket;
        }

        $pk = $this->dir . $name;
        $pk = new $pk;
        
        if (!$oldPacket instanceof DataPacket) {
            // I need to change this to be more dynamic
            echo "[MULTIVERSION]: Packet change requested on non datapacket typing. {$oldPacket->getName()} | " . $oldPacket::NETWORK_ID . "\n";
        }

        if (isset($pk->customTranslation)) $pk = $pk->translateCustomPacket($oldPacket);

        $pk->setBuffer($oldPacket->buffer, $oldPacket->offset);
        $oldPacket = $pk;
        MainLogger::getLogger()->info("§6[MultiVersion] DEBUG: Modified Packet §8[§f {$oldPacket->getName()} §8| §f".$oldPacket::NETWORK_ID."§8]§6 §a{$type}§6.");
        return $oldPacket;
    }

    public function translateLogin($packet) {
        if (!isset($this->protocolPackets['LoginPacket'])) {
            return $oldPacket;
        } else {
            $pk = $this->dir . 'LoginPacket';
            $pk = new $pk;
            $pk->translateLogin($packet);
            $pk->setBuffer($packet->buffer, $packet->offset);
            $packet = $pk;
            return $packet;
        }
    }
}