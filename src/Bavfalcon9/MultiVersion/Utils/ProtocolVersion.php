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
namespace Bavfalcon9\MultiVersion\Utils;

use pocketmine\network\mcpe\DataPacket;

class ProtocolVersion {
    private $protocol;
    private $protocolPackets = [];
    private $restricted = false;
    private $dir = '';
    private $minecraftVersion = '1.13.0';

    /**
     * @return Void
     */
    public function __construct(Float $protocol, String $MCPE, Bool $restrict=false) {
        $this->protocol = $protocol;
        $this->dir = "Bavfalcon9\\MultiVersion\\Protocols\\".$MCPE."\\Packets\\";
        $this->restricted = $restricted;
        $this->minecraftVersion = $MCPE;
    }

    public function setProtocolPackets(Array $packets) {
        $this->protocolPackets = $packets;
    }

    public function getProtocol(): Float {
        return $this->protocol;
    }

    public function getProtocolPackets(): Array {
        return $this->protocolPackets;
    }

    public function getMinecraftVersion(): String {
        return $this->minecraftVersion;
    }

    public function changePacket(String &$name, DataPacket &$oldPacket): ?DataPacket {
        if (!isset($this->protocolPackets[$name]) && $this->restricted === true) return null;
        if (!isset($this->protocolPackets[$name])) {
            return $oldPacket;
        }
        $pk = $this->dir . $this->protocolPackets[$name];
        $pk = new $pk;
        
        if(!$pk instanceof DataPacket) return false;

        $pk->setBuffer($oldPacket->buffer, $oldPacket->offset);
        $oldPacket = $pk;
        return $oldPacket;
    }
}