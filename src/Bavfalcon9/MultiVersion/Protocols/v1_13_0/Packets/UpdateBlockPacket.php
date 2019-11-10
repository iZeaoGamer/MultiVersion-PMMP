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

namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\Packets;

use Bavfalcon9\MultiVersion\Protocols\CustomTranslator;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\types\RuntimeBlockMapping;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping as PMRuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket as PMUpdateBlock;

class UpdateBlockPacket extends DataPacket implements CustomTranslator{
    public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PACKET;

    public const FLAG_NONE = 0b0000;
    public const FLAG_NEIGHBORS = 0b0001;
    public const FLAG_NETWORK = 0b0010;
    public const FLAG_NOGRAPHIC = 0b0100;
    public const FLAG_PRIORITY = 0b1000;

    public const FLAG_ALL = self::FLAG_NEIGHBORS | self::FLAG_NETWORK;
    public const FLAG_ALL_PRIORITY = self::FLAG_ALL | self::FLAG_PRIORITY;

    public const DATA_LAYER_NORMAL = 0;
    public const DATA_LAYER_LIQUID = 1;

    /** @var int */
    public $x;
    /** @var int */
    public $z;
    /** @var int */
    public $y;
    /** @var int */
    public $blockRuntimeId;
    /** @var int */
    public $flags;
    /** @var int */
    public $dataLayerId = self::DATA_LAYER_NORMAL;

    protected function decodePayload(){
        $this->getBlockPosition($this->x, $this->y, $this->z);
        $this->blockRuntimeId = $this->getUnsignedVarInt();
        $this->flags = $this->getUnsignedVarInt();
        $this->dataLayerId = $this->getUnsignedVarInt();
    }

    protected function encodePayload(){
        $this->putBlockPosition($this->x, $this->y, $this->z);
        $this->putUnsignedVarInt($this->blockRuntimeId);
        $this->putUnsignedVarInt($this->flags);
        $this->putUnsignedVarInt($this->dataLayerId);
    }

    public function handle(NetworkSession $session) : bool {
        return $session->handleUpdateBlock($this);
    }

    /**
     * @param PMUpdateBlock $packet
     *
     * @return $this
     */
    public function translateCustomPacket(&$packet) {
        $this->x = $packet->x;
        $this->y = $packet->y;
        $this->z = $packet->z;
        list($id, $meta) = PMRuntimeBlockMapping::fromStaticRuntimeId($packet->blockRuntimeId);
        $this->blockRuntimeId = RuntimeBlockMapping::toStaticRuntimeId($id, $meta);
        $this->flags = $packet->flags;
        $this->dataLayerId = $packet->dataLayerId;

        return $this;
    }
}