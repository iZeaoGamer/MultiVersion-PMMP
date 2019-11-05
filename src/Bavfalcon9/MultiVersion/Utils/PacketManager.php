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

use Bavfalcon9\MultiVersion\Main;
use pocketmine\network\mcpe\PlayerNetworkSessionAdapter;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;

class PacketManager {
    /** @var Array<Float[ProtocolVersion]> */
    private $registered = [];
    /** @var Main */
    private $plugin;
    /** @var Array<String> */
    private $oldplayers = [];
    private $queue = []; // Packet queue to prevent duplications

    public function __construct(Main $pl) {
        $this->plugin = $pl;
    }

    public function registerProtocol(ProtocolVersion $pv): Bool {
        if (isset($this->registered[$pv->getProtocol()])) return false;
        $this->registered[$pv->getProtocol()] = $pv;
        return true;
    }

    public function unregisterProtocol(ProtocolVersion $pv): Bool {
        if (!isset($this->registered[$pv->getProtocol()])) return false;
        unset($this->registered[$pv->getProtocol()]);
        return true;
    }

    public function handlePacketRecieve(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        $nId = $packet::NETWORK_ID;

        if ($packet instanceof LoginPacket) {
            $protocol = $packet->protocol;
            if ($protocol !== ProtocolInfo::CURRENT_PROTOCOL) {
                /* check versions */
                if (!isset($this->registered[$protocol])) {
                    // Protocol not supported.
                    $this->plugin->getLogger()->info("[MULTIVERSION]: {$protocol->username} tried to join with protocol: {$protocol}");
                    $player->close('', '[MultiVersion]: Your game version is not yet supported here.');
                    $ev->setCancelled();
                    return;
                } else {
                    $this->oldplayers[$packet->username] = $protocol;
                    // LOGIN HANDLE?
                }
            }
        }

        if (!isset($this->oldplayers[$player->getName()])) return;
        if (!isset($this->queue[$player->getName()])) $this->queue[$player->getName()];

        $protocol = $this->oldplayers[$player->getName()];
        $protocol = $this->registered[$protocol];
        $protocol->changePacket($protocol->getPacketName($nId), $packet);
        $this->handleOldRecieved($packet, $player);
        $event->setCancelled();
        return;

    }

    public function handlePacketSent(DataPacketSendEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
        $nId = $packet::NETWORK_ID;
        
        if (!in_array($nId, $this->queue[$player->getName()])) {
            $protocol = $this->oldplayers[$player->getName()];
            $protocol = $this->registered[$protocol];
            $success = $protocol->changePacket($protocol->getPacketName($nId), $packet);
            
            if (!$success) {
                $this->plugin->getLogger()->info("[MULTIVERSION]: {$player->getName()} sent an unknown packet with id: {$nId}");
                return;
            }
            
            $player->sendDataPacket($packet);
            array_push($this->queue[$player->getName()], $nId);
            $event->setCancelled();
            return;
        } else {
            unset($this->queue[$player->getName()][$nId]);
        }

        return;
    }

    private function handleOldRecieved(DataPacket $packet, Player $player) {
        $adapter = new PlayerNetworkSessionAdapter($this->plugin->getServer(), $player);
		$adapter->handleDataPacket($packet);
    }
}