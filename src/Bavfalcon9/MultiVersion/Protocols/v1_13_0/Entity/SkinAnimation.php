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