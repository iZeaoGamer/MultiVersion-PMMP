<?php
/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/
namespace Bavfalcon9\MultiVersion\Protocols\v1_13_0\Entity;
class SkinAnimation{
	/** @var SerializedImage */
	private $image;
	/** @var int */
	private $type;
	/** @var float */
	private $frames;
	public function __construct(SerializedImage $image, int $type, float $frames){
		$this->image = $image;
		$this->type = $type;
		$this->frames = $frames;
	}
	public function getImage() : SerializedImage{
		return $this->image;
	}
	public function getType() : int{
		return $this->type;
	}
	public function getFrames() : float{
		return $this->frames;
	}
}