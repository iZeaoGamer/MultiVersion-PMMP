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

namespace Bavfalcon9\MultiVersion\Protocols\v1_12_0\Packets;

use Bavfalcon9\MultiVersion\Protocols\CustomTranslator;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket as PMPlayerList;
use Bavfalcon9\MultiVersion\Protocols\v1_12_0\Entity\Skin;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use function count;

class PlayerListPacket extends DataPacket implements CustomTranslator{
    public const NETWORK_ID = 0x3f;

    public const TYPE_ADD = 0;
    public const TYPE_REMOVE = 1;

    /** @var PlayerListEntry[] */
    public $entries = [];
    /** @var int */
    public $type;

    public function clean(){
        $this->entries = [];

        return parent::clean();
    }

    protected function decodePayload(){
        $this->type = $this->getByte();
        $count = $this->getUnsignedVarInt();
        for($i = 0; $i < $count; ++$i){
            $entry = new PlayerListEntry();
            if($this->type === self::TYPE_ADD){
                $entry->uuid = $this->getUUID();
                $entry->entityUniqueId = $this->getEntityUniqueId();
                $entry->username = $this->getString();
                $skinId = $this->getString();
                $skinData = $this->getString();
                $capeData = $this->getString();
                $geometryName = $this->getString();
                $geometryData = $this->getString();
                $entry->skin = new Skin(
                    $skinId,
                    $skinData,
                    $capeData,
                    $geometryName,
                    $geometryData
                );
                $entry->xboxUserId = $this->getString();
                $entry->platformChatId = $this->getString();
            }else{
                $entry->uuid = $this->getUUID();
            }
            $this->entries[$i] = $entry;
        }
    }

    protected function encodePayload(){
        $this->putByte($this->type);
        $this->putUnsignedVarInt(count($this->entries));
        foreach($this->entries as $entry){
            if($this->type === self::TYPE_ADD){
                $this->putUUID($entry->uuid);
                $this->putEntityUniqueId($entry->entityUniqueId);
                $this->putString($entry->username);
                $this->putString($entry->skin->getSkinId());
                $this->putString($entry->skin->getSkinData()->getData());
                $this->putString($entry->skin->getCapeData()->getData());
                $this->putString("");
                $this->putString($entry->skin->getGeometryData());
                $this->putString($entry->xboxUserId);
                $this->putString($entry->platformChatId);
            }else{
                $this->putUUID($entry->uuid);
            }
        }
    }

    public function handle(NetworkSession $session) : bool{
        return $session->handlePlayerList($this);
    }

    /**
     * @param PMPlayerList $packet
     *
     * @return $this
     */
    public function translateCustomPacket(&$packet){
        $this->type = $packet->type;
        foreach($packet->entries as $entry){
            $cache = $entry->skin;
            $skinData = $cache->getSkinData()->getData();
            $capeData = $cache->getCapeData()->getData();
            $skinId = $cache->getSkinId();
            $geometryName = "Steve";
            $geometryData = $cache->getGeometryData();
            $entry->skin = new Skin(
                $skinId,
                $skinData,
                $capeData,
                $geometryName,
                $geometryData
            );
        };

        return $this;
    }
}