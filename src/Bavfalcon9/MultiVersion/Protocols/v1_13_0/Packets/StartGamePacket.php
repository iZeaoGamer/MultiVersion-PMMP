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

use Bavfalcon9\MultiVersion\Protocols\v1_13_0\types\RuntimeBlockMapping;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\StartGamePacket as PMStartGame;
use function count;
use function file_get_contents;
use function json_decode;

class StartGamePacket extends DataPacket{
    public const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

    public $customTranslation = true;

	/** @var string|null */
	private static $blockTableCache = null;
	/** @var string|null */
	private static $itemTableCache = null;

	/** @var int */
	public $entityUniqueId;
	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $playerGamemode;
	/** @var Vector3 */
	public $playerPosition;
	/** @var float */
	public $pitch;
	/** @var float */
	public $yaw;
	/** @var int */
	public $seed;
	/** @var int */
	public $dimension;
	/** @var int */
	public $generator = 1; //default infinite - 0 old, 1 infinite, 2 flat
	/** @var int */
	public $worldGamemode;
	/** @var int */
	public $difficulty;
	/** @var int */
	public $spawnX;
	/** @var int */
	public $spawnY;
	/** @var int */
	public $spawnZ;
	/** @var bool */
	public $hasAchievementsDisabled = true;
	/** @var int */
	public $time = -1;
	/** @var int */
	public $eduEditionOffer = 0;
	/** @var bool */
	public $hasEduFeaturesEnabled = false;
	/** @var float */
	public $rainLevel;
	/** @var float */
	public $lightningLevel;
	/** @var bool */
	public $hasConfirmedPlatformLockedContent = false;
	/** @var bool */
	public $isMultiplayerGame = true;
	/** @var bool */
	public $hasLANBroadcast = true;
	/** @var int */
	public $xboxLiveBroadcastMode = 0; //TODO: find values
	/** @var int */
	public $platformBroadcastMode = 0;
	/** @var bool */
	public $commandsEnabled;
	/** @var bool */
	public $isTexturePacksRequired = true;
	/** @var array */
	public $gameRules = [ //TODO: implement this
		"naturalregeneration" => [1, false] //Hack for client side regeneration
	];
	/** @var bool */
	public $hasBonusChestEnabled = false;
	/** @var bool */
	public $hasStartWithMapEnabled = false;
	/** @var int */
	public $defaultPlayerPermission = PlayerPermissions::MEMBER; //TODO
	/** @var int */
	public $serverChunkTickRadius = 4; //TODO (leave as default for now)
	/** @var bool */
	public $hasLockedBehaviorPack = false;
	/** @var bool */
	public $hasLockedResourcePack = false;
	/** @var bool */
	public $isFromLockedWorldTemplate = false;
	/** @var bool */
	public $useMsaGamertagsOnly = false;
	/** @var bool */
	public $isFromWorldTemplate = false;
	/** @var bool */
	public $isWorldTemplateOptionLocked = false;
	/** @var bool */
	public $onlySpawnV1Villagers = false;
	/** @var string */
	public $vanillaVersion = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
	/** @var string */
	public $levelId = ""; //base64 string, usually the same as world folder name in vanilla
	/** @var string */
	public $worldName;
	/** @var string */
	public $premiumWorldTemplateId = "";
	/** @var bool */
	public $isTrial = false;
	/** @var bool */
	public $isMovementServerAuthoritative = false;
	/** @var int */
	public $currentTick = 0; //only used if isTrial is true
	/** @var int */
	public $enchantmentSeed = 0;
	/** @var string */
	public $multiplayerCorrelationId = ""; //TODO: this should be filled with a UUID of some sort
	/** @var array|null ["name" (string), "data" (int16), "legacy_id" (int16)] */
	public $blockTable = null;
	/** @var array|null string (name) => int16 (legacyID) */
	public $itemTable = null;

	protected function decodePayload(){
		$this->entityUniqueId = $this->getEntityUniqueId();
		$this->entityRuntimeId = $this->getEntityRuntimeId();
		$this->playerGamemode = $this->getVarInt();
		$this->playerPosition = $this->getVector3();
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		//Level settings
		$this->seed = $this->getVarInt();
		$this->dimension = $this->getVarInt();
		$this->generator = $this->getVarInt();
		$this->worldGamemode = $this->getVarInt();
		$this->difficulty = $this->getVarInt();
		$this->getBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->hasAchievementsDisabled = $this->getBool();
		$this->time = $this->getVarInt();
		$this->eduEditionOffer = $this->getVarInt();
		$this->hasEduFeaturesEnabled = $this->getBool();
		$this->rainLevel = $this->getLFloat();
		$this->lightningLevel = $this->getLFloat();
		$this->hasConfirmedPlatformLockedContent = $this->getBool();
		$this->isMultiplayerGame = $this->getBool();
		$this->hasLANBroadcast = $this->getBool();
		$this->xboxLiveBroadcastMode = $this->getVarInt();
		$this->platformBroadcastMode = $this->getVarInt();
		$this->commandsEnabled = $this->getBool();
		$this->isTexturePacksRequired = $this->getBool();
		$this->gameRules = $this->getGameRules();
		$this->hasBonusChestEnabled = $this->getBool();
		$this->hasStartWithMapEnabled = $this->getBool();
		$this->defaultPlayerPermission = $this->getVarInt();
		$this->serverChunkTickRadius = $this->getLInt();
		$this->hasLockedBehaviorPack = $this->getBool();
		$this->hasLockedResourcePack = $this->getBool();
		$this->isFromLockedWorldTemplate = $this->getBool();
		$this->useMsaGamertagsOnly = $this->getBool();
		$this->isFromWorldTemplate = $this->getBool();
		$this->isWorldTemplateOptionLocked = $this->getBool();
		$this->onlySpawnV1Villagers = $this->getBool();
		$this->vanillaVersion = $this->getString();
		$this->levelId = $this->getString();
		$this->worldName = $this->getString();
		$this->premiumWorldTemplateId = $this->getString();
		$this->isTrial = $this->getBool();
		$this->isMovementServerAuthoritative = $this->getBool();
		$this->currentTick = $this->getLLong();
		$this->enchantmentSeed = $this->getVarInt();
		$this->blockTable = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$id = $this->getString();
			$data = $this->getSignedLShort();
			$unknown = $this->getSignedLShort();
			$this->blockTable[$i] = ["name" => $id, "data" => $data, "legacy_id" => $unknown];
		}

		$this->itemTable = [];
		for($i = 0, $count = $this->getUnsignedVarInt(); $i < $count; ++$i){
			$id = $this->getString();
			$legacyId = $this->getSignedLShort();
			$this->itemTable[$id] = $legacyId;
		}

		$this->multiplayerCorrelationId = $this->getString();
	}

	protected function encodePayload(){
		$this->putEntityUniqueId($this->entityUniqueId);
		$this->putEntityRuntimeId($this->entityRuntimeId);
		$this->putVarInt($this->playerGamemode);
		$this->putVector3($this->playerPosition);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		//Level settings
		$this->putVarInt($this->seed);
		$this->putVarInt($this->dimension);
		$this->putVarInt($this->generator);
		$this->putVarInt($this->worldGamemode);
		$this->putVarInt($this->difficulty);
		$this->putBlockPosition($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->putBool($this->hasAchievementsDisabled);
		$this->putVarInt($this->time);
		$this->putVarInt($this->eduEditionOffer);
		$this->putBool($this->hasEduFeaturesEnabled);
		$this->putLFloat($this->rainLevel);
		$this->putLFloat($this->lightningLevel);
		$this->putBool($this->hasConfirmedPlatformLockedContent);
		$this->putBool($this->isMultiplayerGame);
		$this->putBool($this->hasLANBroadcast);
		$this->putVarInt($this->xboxLiveBroadcastMode);
		$this->putVarInt($this->platformBroadcastMode);
		$this->putBool($this->commandsEnabled);
		$this->putBool($this->isTexturePacksRequired);
		$this->putGameRules($this->gameRules);
		$this->putBool($this->hasBonusChestEnabled);
		$this->putBool($this->hasStartWithMapEnabled);
		$this->putVarInt($this->defaultPlayerPermission);
		$this->putLInt($this->serverChunkTickRadius);
		$this->putBool($this->hasLockedBehaviorPack);
		$this->putBool($this->hasLockedResourcePack);
		$this->putBool($this->isFromLockedWorldTemplate);
		$this->putBool($this->useMsaGamertagsOnly);
		$this->putBool($this->isFromWorldTemplate);
		$this->putBool($this->isWorldTemplateOptionLocked);
		$this->putBool($this->onlySpawnV1Villagers);
		$this->putString($this->vanillaVersion);
		$this->putString($this->levelId);
		$this->putString($this->worldName);
		$this->putString($this->premiumWorldTemplateId);
		$this->putBool($this->isTrial);
		$this->putBool($this->isMovementServerAuthoritative);
		$this->putLLong($this->currentTick);
		$this->putVarInt($this->enchantmentSeed);
		if($this->blockTable === null){
			if(self::$blockTableCache === null){
				//this is a really nasty hack, but it'll do for now
				self::$blockTableCache = self::serializeBlockTable(RuntimeBlockMapping::getBedrockKnownStates());
			}

			$this->put(self::$blockTableCache);
		}else{
			$this->put(self::serializeBlockTable($this->blockTable));
		}

		if($this->itemTable === null){
			if(self::$itemTableCache === null){
				self::$itemTableCache = self::serializeItemTable(json_decode(file_get_contents(MULTIVERSION_v1_13_0 . '/item_id_map.json'), true));
			}

			$this->put(self::$itemTableCache);
		}else{
			$this->put(self::serializeItemTable($this->itemTable));
		}

		$this->putString($this->multiplayerCorrelationId);
	}

	private static function serializeBlockTable(array $table) : string{
		$states = new ListTag();
		foreach($table as $v){
			$state = new CompoundTag();
			$state->setTag(new CompoundTag("block", [
				new StringTag("name", $v["name"]),
				$v["states"]
			]));
			$state->setShort("id", $v["legacy_id"]);
			$states->push($state);
		}

		($stream = new NetworkLittleEndianNBTStream())->writeTag($states);
		return $stream->buffer;
	}

	private static function serializeItemTable(array $table) : string{
		$stream = new NetworkBinaryStream();
		$stream->putUnsignedVarInt(count($table));
		foreach($table as $name => $legacyId){
			$stream->putString($name);
			$stream->putLShort($legacyId);
		}

		return $stream->getBuffer();
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleStartGame($this);
    }

	public function translateCustomPacket(PMStartGame $packet) {
		$this->spawnX = $packet->spawnX;
		$this->spawnY = $packet->spawnY;
		$this->spawnZ = $packet->spawnZ;
		$this->entityUniqueId = $packet->entityUniqueId;
		$this->entityRuntimeId = $packet->entityRuntimeId;
		$this->playerGamemode = $packet->playerGamemode;
		$this->playerPosition = $packet->playerPosition;
		$this->pitch = $packet->pitch;
		$this->yaw = $packet->yaw;
		//Level settings
		$this->seed = $packet->seed;
		$this->dimension = $packet->dimension;
		$this->generator = $packet->generator;
		$this->worldGamemode = $packet->worldGamemode;
		$this->difficulty = $packet->difficulty;
		$this->hasAchievementsDisabled = $packet->hasAchievementsDisabled;
		$this->time = $packet->time;
		$this->hasEduFeaturesEnabled = $packet->hasEduFeaturesEnabled;
		$this->rainLevel = $packet->rainLevel;
		$this->lightningLevel = $packet->lightningLevel;
		$this->hasConfirmedPlatformLockedContent = $packet->hasConfirmedPlatformLockedContent;
		$this->isMultiplayerGame = $packet->isMultiplayerGame;
		$this->hasLANBroadcast = $packet->hasLANBroadcast;
		$this->xboxLiveBroadcastMode = $packet->xboxLiveBroadcastMode;
		$this->platformBroadcastMode = $packet->platformBroadcastMode;
		$this->commandsEnabled = $packet->commandsEnabled;
		$this->isTexturePacksRequired = $packet->isTexturePacksRequired;
		$this->gameRules = $packet->gameRules;
		$this->hasBonusChestEnabled = $packet->hasBonusChestEnabled;
		$this->hasStartWithMapEnabled = $packet->hasStartWithMapEnabled;
		$this->defaultPlayerPermission = $packet->defaultPlayerPermission;
		$this->serverChunkTickRadius = $packet->serverChunkTickRadius;
		$this->hasLockedBehaviorPack = $packet->hasLockedBehaviorPack;
		$this->hasLockedResourcePack = $packet->hasLockedResourcePack;
		$this->isFromLockedWorldTemplate = $packet->isFromLockedWorldTemplate;
		$this->useMsaGamertagsOnly = $packet->useMsaGamertagsOnly;
		$this->isFromWorldTemplate = $packet->isFromWorldTemplate;
		$this->isWorldTemplateOptionLocked = $packet->isWorldTemplateOptionLocked;
		$this->onlySpawnV1Villagers = $packet->onlySpawnV1Villagers;
		$this->levelId = $packet->levelId;
		$this->worldName = $packet->worldName;
		$this->premiumWorldTemplateId = $packet->premiumWorldTemplateId;
		$this->isTrial = $packet->isTrial;
		$this->currentTick = $packet->currentTick;
		$this->enchantmentSeed = $packet->enchantmentSeed;
		$this->blockTable = $packet->blockTable;

		return $this;
	}
}