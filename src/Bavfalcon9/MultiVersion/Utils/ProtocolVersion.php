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

namespace Bavfalcon9\MultiVersion\Utils;

use Bavfalcon9\MultiVersion\Protocols\CustomTranslator;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\utils\MainLogger;
use function explode;
use function implode;

class ProtocolVersion {
    public const DEVELOPER = true; // set to true for debug
    public const VERSIONS = [
        '1.12.0' => 361,
        '1.13.0' => 388
    ];

    /** @var int */
    private $protocol;
    /** @var array */
    private $protocolPackets = [];
    /** @var bool */
    private $restricted = false;
    /** @var string */
    private $dir = '';
    /** @var string */
    private $dirPath = '';
    /** @var string */
    private $minecraftVersion = '1.13.0';
    /** @var array */
    private $packetListeners = [];

    /**
     * @param int    $protocol
     * @param String $MCPE
     * @param bool   $restrict
     * @param array  $listeners
     */
    public function __construct(int $protocol, String $MCPE, Bool $restrict = false, Array $listeners = []) {
        $fixedMCPE = 'v'.implode('_', explode('.', $MCPE));
        $this->protocol = $protocol;
        $this->dirPath = "Bavfalcon9\\MultiVersion\\Protocols\\".$fixedMCPE."\\";
        $this->dir = $this->dirPath . "Packets\\";
        $this->restricted = $restrict;
        $this->minecraftVersion = $MCPE;
        $this->wantedListeners = $listeners;
        $this->registerListeners();
    }

    public function setProtocolPackets(Array $packets) {
        $this->protocolPackets = $packets;
    }

    public function setListeners(Array $listeners) {
        $this->wantedListeners = $listeners;
        $this->registerListeners();
    }

    public function addPacketListener(PacketListener $listener): Bool {
        if (isset($this->packetListeners[$listener->getName()])) {
            return false;
        } else {
            $this->packetListeners[$listener->getName()] = $listener;
            return true;
        }
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
        foreach ($this->protocolPackets as $name => $pid) {
            if ($id == $pid) {
                return $name;
            }
        }

        return "$id";
    }

    public function changePacket(String &$name, &$oldPacket, String $type = 'Sent') {
        $modified = false;
        foreach ($this->packetListeners as $listener) {
            if ($listener->getPacketName() === $oldPacket->getName() && $oldPacket::NETWORK_ID === $listener->getPacketNetworkID()) {
                $success = $listener->onPacketCheck($oldPacket);
                if (!$success) {
                    continue;
                } else {
                    $listener->onPacketMatch($oldPacket);
                    $modified = true;
                    continue;
                }
            }
        }

        if ($modified) {
            return $oldPacket;
        }

        if (!isset($this->protocolPackets[$name]) && $this->restricted === true) {
            return null;
        }

        if (!isset($this->protocolPackets[$name])) {
            if (self::DEVELOPER === true) {
                MainLogger::getLogger()->info("§c[MultiVersion] DEBUG:§e Packet §8[§f {$oldPacket->getName()} §8| §f".$oldPacket::NETWORK_ID."§8]§e requested a change but no change supported §a{$type}§e.");
            }

            return $oldPacket;
        }

        $pk = $this->dir . $name;
        $pk = new $pk;
        $pk->setBuffer($oldPacket->buffer, $oldPacket->offset);

        if (!$oldPacket instanceof DataPacket) {
            // I need to change this to be more dynamic
            MainLogger::getLogger()->info("§8[MULTIVERSION]: Packet change requested on non DataPacket typing. {$oldPacket->getName()} | " . $oldPacket::NETWORK_ID);
        }

        if ($pk instanceof CustomTranslator) {
            $pk = $pk->translateCustomPacket($oldPacket);
        }

        $oldPacket = $pk;
        MainLogger::getLogger()->info("§6[MultiVersion] DEBUG: Modified Packet §8[§f {$oldPacket->getName()} §8| §f".$oldPacket::NETWORK_ID."§8]§6 §a{$type}§6.");

        return $oldPacket;
    }

    public function translateLogin($packet) {
        if (!isset($this->protocolPackets['LoginPacket'])) {
            return $packet;
        } else {
            $pk = $this->dir . 'LoginPacket';
            $pk = new $pk;
            $pk->translateLogin($packet);
            $pk->setBuffer($packet->buffer, $packet->offset);

            return $pk;
        }
    }

    public function registerListeners() {
        foreach ($this->wantedListeners as $lsn) {
            $listener = $this->dirPath . "PacketListeners\\$lsn";
            $listener = new $listener;
            $this->addPacketListener($listener);
        }
    }
}