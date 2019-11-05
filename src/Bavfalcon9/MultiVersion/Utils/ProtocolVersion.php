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
        $fixedMCPE = 'v'.implode('_', explode('.', $MCPE));
        $this->protocol = $protocol;
        $this->dir = "Bavfalcon9\\MultiVersion\\Protocols\\".$fixedMCPE."\\Packets\\";
        $this->restricted = $restrict;
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

    public function getPacketName(Float $id): ?String {
        foreach ($this->protocolPackets as $name=>$pid) {
            if ($id == $pid) return $name;
            else continue;
        }

        return '';
    }

    public function changePacket(String &$name, &$oldPacket) {
        if (!isset($this->protocolPackets[$name]) && $this->restricted === true) return null;
        if (!isset($this->protocolPackets[$name])) {
            return $oldPacket;
        }
        $pk = $this->dir . $name;
        $pk = new $pk;
        
        $pk->setBuffer($oldPacket->buffer, $oldPacket->offset);
        $oldPacket = $pk;
        return $oldPacket;
    }
}