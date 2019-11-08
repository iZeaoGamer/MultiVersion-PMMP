<?php

namespace Bavfalcon9\MultiVersion\Protocols\v1_12_0\Packets;

use Bavfalcon9\MultiVersion\Protocols\v1_12_0\Entity\Skin;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket as PMPLayerSkin;
use pocketmine\utils\UUID;

class PlayerSkinPacket extends DataPacket{
    public const NETWORK_ID = 0x5d;
    public $customTranslation = true;
    /** @var UUID */
    public $uuid;
    /** @var string */
    public $oldSkinName = "";
    /** @var string */
    public $newSkinName = "";
    /** @var Skin */
    public $skin;
    /** @var bool */
    public $premiumSkin = false;

    protected function decodePayload(){
        $this->uuid = $this->getUUID();
        $skinId = $this->getString();
        $this->newSkinName = $this->getString();
        $this->oldSkinName = $this->getString();
        $skinData = $this->getString();
        $capeData = $this->getString();
        $geometryModel = $this->getString();
        $geometryData = $this->getString();
        $this->skin = new Skin($skinId, $skinData, $capeData, $geometryModel, $geometryData);
        $this->premiumSkin = $this->getBool();
    }
    protected function encodePayload(){
        $this->putUUID($this->uuid);
        $this->putString($this->skin->getSkinId());
        $this->putString($this->newSkinName);
        $this->putString($this->oldSkinName);
        $this->putString($this->skin->getSkinData());
        $this->putString($this->skin->getCapeData());
        $this->putString($this->skin->getGeometryName());
        $this->putString($this->skin->getGeometryData());
        $this->putBool($this->premiumSkin);
    }
    public function handle(NetworkSession $session) : bool{
        return $session->handlePlayerSkin($this);
    }

    public function translateCustomPacket(PMPLayerSkin $packet){
        $skin = $packet->skin;
        $this->uuid = $packet->uuid;
        $this->oldSkinName = "";
        $this->newSkinName = "";
        $skinData = $skin->getSkinData();
        $capeData = $skin->getCapeData();
        $skinId = $skin->getSkinId();
        $geometryName = "Steve";
        $geometryData = $skin->getGeometryData();
        $this->skin = new Skin(
            $skinId,
            $skinData,
            $capeData,
            $geometryName,
            $geometryData
        );

        return $this;
    }
}