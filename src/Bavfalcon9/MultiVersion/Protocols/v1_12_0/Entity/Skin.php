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

namespace Bavfalcon9\MultiVersion\Protocols\v1_12_0\Entity;

use Ahc\Json\Comment as CommentedJsonDecoder;
use function implode;
use function in_array;
use function json_encode;
use function strlen;
class Skin{
	public const ACCEPTED_SKIN_SIZES = [
		64 * 32 * 4,
		64 * 64 * 4,
		128 * 128 * 4
	];
	/** @var string */
	private $skinId;
	/** @var string */
	private $skinData;
	/** @var string */
	private $capeData;
	/** @var string */
	private $geometryName;
	/** @var string */
	private $geometryData;

    /**
     * @param string $skinId
     * @param string $skinData
     * @param string $capeData
     * @param string $geometryName
     * @param string $geometryData
     */
    public function __construct(string $skinId, string $skinData, string $capeData = "", string $geometryName = "", string $geometryData = ""){
		$this->skinId = $skinId;
		$this->skinData = $skinData;
		$this->capeData = $capeData;
		$this->geometryName = $geometryName;
		$this->geometryData = $geometryData;
	}
	/**
	 * @deprecated
	 * @return bool
	 */
	public function isValid() : bool{
		try{
			$this->validate();
			return true;
		}catch(\InvalidArgumentException $e){
			return false;
		}
	}
	/**
	 * @throws \InvalidArgumentException
	 */
	public function validate() : void{
		if($this->skinId === ""){
			throw new \InvalidArgumentException("Skin ID must not be empty");
		}
		$len = strlen($this->skinData);
		if(!in_array($len, self::ACCEPTED_SKIN_SIZES, true)){
			throw new \InvalidArgumentException("Invalid skin data size $len bytes (allowed sizes: " . implode(", ", self::ACCEPTED_SKIN_SIZES) . ")");
		}
		if($this->capeData !== "" and strlen($this->capeData) !== 8192){
			throw new \InvalidArgumentException("Invalid cape data size " . strlen($this->capeData) . " bytes (must be exactly 8192 bytes)");
		}
		//TODO: validate geometry
	}
	/**
	 * @return string
	 */
	public function getSkinId() : string{
		return $this->skinId;
	}
	/**
	 * @return string
	 */
	public function getSkinData() : string{
		return $this->skinData;
	}
	/**
	 * @return string
	 */
	public function getCapeData() : string{
		return $this->capeData;
	}
	/**
	 * @return string
	 */
	public function getGeometryName() : string{
		return $this->geometryName;
	}
	/**
	 * @return string
	 */
	public function getGeometryData() : string{
		return $this->geometryData;
	}
	/**
	 * Hack to cut down on network overhead due to skins, by un-pretty-printing geometry JSON.
	 *
	 * Mojang, some stupid reason, send every single model for every single skin in the selected skin-pack.
	 * Not only that, they are pretty-printed.
	 * TODO: find out what model crap can be safely dropped from the packet (unless it gets fixed first)
	 */
	public function debloatGeometryData() : void{
		if($this->geometryData !== ""){
			$this->geometryData = (string) json_encode((new CommentedJsonDecoder())->decode($this->geometryData));
		}
	}
}