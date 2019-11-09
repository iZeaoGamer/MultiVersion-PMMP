<?php

namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\PacketListeners;

use Bavfalcon9\MultiVersion\Listener;
use Bavfalcon9\MultiVersion\PacketListener;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class BatchPacketListenerTest extends PacketListener implements Listener {
    public function __construct() {
        parent::__construct('BatchPacket', ProtocolInfo::BATCH_PACKET);
    }

    public function onPacketCheck(&$packet): Bool {
        return isset($packet->yaw);
    }

    public function onPacketMatch(&$packet): Void {
        // a simple test
        $packet->yaw = floor($packet->yaw / 2);
        return;
    }
}