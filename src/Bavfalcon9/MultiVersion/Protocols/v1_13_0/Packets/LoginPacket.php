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

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;
use pocketmine\network\mcpe\protocol\LoginPacket as PMLogin;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

class LoginPacket extends PMLogin{

	public const NETWORK_ID = 0x01;
    public const EDITION_POCKET = 0;

	/** @var string */
	public $username;
	/** @var int */
	public $protocol;
	/** @var string */
	public $clientUUID;
	/** @var int */
	public $clientId;
	/** @var string */
	public $xuid;
	/** @var string */
	public $identityPublicKey;
	/** @var string */
	public $serverAddress;
	/** @var string */
	public $locale;
	/** @var array (the "chain" index contains one or more JWTs) */
	public $chainData = [];
	/** @var string */
	public $clientDataJwt;
	/** @var array decoded payload of the clientData JWT */
	public $clientData = [];

	/**
	 * This field may be used by plugins to bypass keychain verification. It should only be used for plugins such as
	 * Specter where passing verification would take too much time and not be worth it.
	 *
	 * @var bool
	 */
	public $skipVerification = false;

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	public function mayHaveUnreadBytes() : bool{
		return $this->protocol !== null and $this->protocol !== ProtocolInfo::CURRENT_PROTOCOL;
	}

	protected function decodePayload(){
		$this->protocol = ((unpack("N", $this->get(4))[1] << 32 >> 32));
		$this->protocol = ProtocolInfo::CURRENT_PROTOCOL; // Hack to allow a bypass
		if($this->protocol !== ProtocolInfo::CURRENT_PROTOCOL){
			if($this->protocol > 0xffff){ //guess MCPE <= 1.1
				$this->offset -= 6;
				$this->protocol = ((unpack("N", $this->get(4))[1] << 32 >> 32));
			}
		}

		try{
			$this->decodeConnectionRequest();
		}catch(\Throwable $e){
			if($this->protocol === ProtocolInfo::CURRENT_PROTOCOL){
				throw $e;
			}

			$logger = MainLogger::getLogger();
			$logger->debug(get_class($e) . " was thrown while decoding connection request in login (protocol version " . ($this->protocol ?? "unknown") . "): " . $e->getMessage());
			foreach(Utils::printableTrace($e->getTrace()) as $line){
				$logger->debug($line);
			}
		}
	}

	protected function decodeConnectionRequest() : void{
		$buffer = new BinaryStream($this->getString());
		$this->chainData = json_decode($buffer->get($buffer->getLInt()), true);
		foreach($this->chainData["chain"] as $chain){
			$webtoken = Utils::decodeJWT($chain);
			if(isset($webtoken["extraData"])){
				if(isset($webtoken["extraData"]["displayName"])){
					$this->username = $webtoken["extraData"]["displayName"];
				}

				if(isset($webtoken["extraData"]["identity"])){
					$this->clientUUID = $webtoken["extraData"]["identity"];
				}

				if(isset($webtoken["extraData"]["XUID"])){
					$this->xuid = $webtoken["extraData"]["XUID"];
				}
			}

			if(isset($webtoken["identityPublicKey"])){
				$this->identityPublicKey = $webtoken["identityPublicKey"];
			}
		}
		$this->clientDataJwt = $buffer->get($buffer->getLInt());
		$this->clientData = Utils::decodeJWT($this->clientDataJwt);
		$this->clientId = $this->clientData["ClientRandomId"] ?? null;
		$this->serverAddress = $this->clientData["ServerAddress"] ?? null;
		$this->locale = $this->clientData["LanguageCode"] ?? null;
	}

	protected function encodePayload(){
		//TODO
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleLogin($this);
    }

    public function translateLogin($packet) {
        // $this->protocol =  Why did i do this?
        $this->protocol = ProtocolInfo::CURRENT_PROTOCOL; // required to assign a temporary bypass through the server.
        $this->clientData = $packet->clientData;
        $this->clientData['SkinGeometry'] = $packet->clientData['SkinGeometryData'];
        return $this;   
    }
}