<?php

namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\PacketListeners;

use Bavfalcon9\MultiVersion\Utils\Listener;
use Bavfalcon9\MultiVersion\Utils\PacketListener;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\BatchPacket;
class UpdateBlockMaps extends PacketListener implements Listener {
    public function __construct() {
        parent::__construct('BatchPacket', BatchPacket::NETWORK_ID);
    }

    public function onPacketCheck(&$packet): Bool {

        return isset);
    }

    public function onPacketMatch(&$packet): Void {

    }
}