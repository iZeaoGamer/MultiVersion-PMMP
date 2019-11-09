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

use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\SerializedImage;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\Skin;
use Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity\SkinAnimation;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class PlayerSkinPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_SKIN_PACKET;

	/** @var UUID */
	public $uuid;
	/** @var Skin */
	public $skin;

	protected function decodePayload(){
		$this->uuid = $this->getUUID();
		$this->skin = $this->getSkin();
	}

	protected function encodePayload(){
		$this->putUUID($this->uuid);
		$this->putSkin($this->skin);
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handlePlayerSkin($this);
    }

    private function getSkin() : Skin{
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

		return new Skin($skinId, $skinResourcePatch, $skinData, $animations, $capeData, $geometryData, $animationData, $premium, $persona, $capeOnClassic, $capeId);
    }

    private function putSkin(Skin $skin) {
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
}