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

class PacketManager {
    /** @var Array<Float[ProtocolVersion]> */
    private $registered = [];
    /** @var Main */
    private $plugin;

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
}