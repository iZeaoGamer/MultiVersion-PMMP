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

namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\Packets;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

use Bavfalcon9\MultiVersion\Protocols\v1_12_0\Entity\Skin;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\Skin as Skin_13;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\SkinAnimation;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\SerializedImage;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use function count;
class PlayerListPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_LIST_PACKET;
	public const TYPE_ADD = 0;
	public const TYPE_REMOVE = 1;
	/** @var PlayerListEntry[] */
	public $entries = [];
	/** @var int */
	public $type;
	public $customTranslation = true; // MUTLIVERSION
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
				$entry->username = $this->getString();
				$entry->xboxUserId = $this->getString();
				$entry->platformChatId = $this->getString();
				$entry->buildPlatform = $this->getLInt();
				$entry->skin = $this->getSkin();
				$entry->entityUniqueId = $this->getEntityUniqueId();
				$entry->isTeacher = false;
				$entry->isHost = false;
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
				$this->putString($entry->xboxUserId);
				$this->putString($entry->platformChatId);
				$this->putLInt($entry->buildPlatform);
				$this->putSkin(Skin_13::null());
				$this->putBool(false);
				$this->putBool(false);
			}else{
				$this->putUUID($entry->uuid);
			}
		}
	}
    private function getSkin() {
		$skinId = $this->getString();
		$skinResourcePatch = $this->getString();
		$skinData = $this->getImage();
		$animationCount = $this->getLInt();
		$animations = [];
		for($i = 0, $count = $animationCount; $i < $count; ++$i){
			$animations[] = new SkinAnimation($this->getImage(), $this->getLInt(), $this->getLFloat());
		}
		$capeData = $this->getImage();
		$geometryData = $this->getString();
		$animationData = $this->getString();
		$premium = $this->getBool();
		$persona = $this->getBool();
		$capeOnClassic = $this->getBool();
		$capeId = $this->getString();
		$fullSkinId = $this->getString();
		return new Skin($skinId, $skinData, $capeData, 'CONVERSION_1.13', $geometryData);
    }
    private function putSkin($skin) {
        $this->putString($skin->getSkinId());
		$this->putString($skin->getSkinResourcePatch());
		$this->putImage($skin->getSkinData());
		$this->putLInt(count($animations = $skin->getAnimations()));
		foreach($animations as $animation){
			$this->putImage($animation->getImage());
			$this->putLInt($animation->getType());
			$this->putLFloat($animation->getFrames());
		}
		$this->putImage($skin->getCapeData());
		$this->putString($skin->getGeometryData());
		$this->putString($skin->getAnimationData());
		$this->putBool($skin->isPremium());
		$this->putBool($skin->isPersona());
		$this->putBool($skin->isCapeOnClassic());
		$this->putString($skin->getCapeId());
		$this->putString($skin->getFullSkinId());
	}
	private function putImage(SerializedImage $image) : void{
		$this->putLInt($image->getWidth());
		$this->putLInt($image->getHeight());
		$this->putString($image->getData());
	}
	private function getImage() : SerializedImage{
		$width = $this->getLInt();
		$height = $this->getLInt();
		$data = $this->getString();
		return new SerializedImage($width, $height, $data);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerList($this);
	}

	public function translateCustomPacket($packet) {
		$this->type = $packet->type;
		foreach ($packet->entries as $entry) {
			$cache = $entry->skin;
			if (!$cache) {
				$entry->skin = Skin_13::null();
			} else {
				$skinData = SerializedImage::fromLegacy($cache->getSkinData());
				$capeData = SerializedImage::fromLegacy($cache->getCapeData());
				$skinId = $cache->getSkinId();
				$geometryName = "Steve";
				$geometryData = $cache->getGeometryData();
				$entry->skin = new Skin_13(
					$skinId,
					'CONVERSION_1_13',
					$skinData,
					[],
					$capeData,
					$geometryData
				);
			}
		};
		return $this;
	}
}
