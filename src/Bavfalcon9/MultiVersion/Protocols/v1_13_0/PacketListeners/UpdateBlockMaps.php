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

// see https://github.com/Olybear9/MultiVersion-PMMP/wiki/Packet-Listeners

declare(strict_types=1);

namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\PacketListeners;

use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Packets\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket as PMUpdateBlock;
use Bavfalcon9\MultiVersion\Utils\PacketListener;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\PacketPool;

class UpdateBlockMaps extends PacketListener {

    public function __construct() {
        parent::__construct('BatchPacket', BatchPacket::NETWORK_ID);
    }

    /**
     * @param BatchPacket $packet
     *
     * @return bool
     */
    public function onPacketCheck(&$packet): Bool {
        foreach($packet->getPackets() as $buf){
            $pk = PacketPool::getPacket($buf);
            if($pk instanceof PMUpdateBlock){
                return true;
            }
        }

        return false;
    }

    /**
     * @param BatchPacket $packet
     *
     * @return void
     */
    public function onPacketMatch(&$packet) : Void {
        foreach($packet->getPackets() as $buf){
            $pk = PacketPool::getPacket($buf);
            if($pk instanceof PMUpdateBlock){
                $newPk = "Bavfalcon9\\MultiVersion\\Protocols\\v1_13_0\\Packets\\UpdateBlockPacket";
                /** @var UpdateBlockPacket $newPk */
                $newPk = new $newPk;

                $pk = $this->decodeUpdateBlockPacketPayload($pk);
                $pk = $newPk->translateCustomPacket($pk);
                $pk = $this->encodeUpdateBlockPacketPayload($pk);

                $newPayload = str_replace(strlen($buf) . $buf, $pk->buffer, $packet->payload);
                $packet->setBuffer($newPayload, $packet->offset);
            }
        }

        return;
    }

    private function encodeUpdateBlockPacketPayload(UpdateBlockPacket $packet){
        $packet->putBlockPosition($packet->x, $packet->y, $packet->z);
        $packet->putUnsignedVarInt($packet->blockRuntimeId);
        $packet->putUnsignedVarInt($packet->flags);
        $packet->putUnsignedVarInt($packet->dataLayerId);

        return $packet;
    }

    private function decodeUpdateBlockPacketPayload(PMUpdateBlock $packet){
        $packet->getBlockPosition($packet->x, $packet->y, $packet->z);
        $packet->blockRuntimeId = $packet->getUnsignedVarInt();
        $packet->flags = $packet->getUnsignedVarInt();
        $packet->dataLayerId = $packet->getUnsignedVarInt();

        return $packet;
    }
}