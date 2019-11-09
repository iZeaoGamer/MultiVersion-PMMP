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

use function ceil;
use function sqrt;
use function strlen;

class SerializedImage{
	/** @var int */
	private $width;
	/** @var int */
	private $height;
	/** @var string */
	private $data;

	public function __construct(int $width, int $height, string $data){
		if(strlen($data) !== ($width * $height) * 4) {
			$width = $height = (int) ceil(sqrt(strlen($data) / 4));
		}

		$this->width = $width;
		$this->height = $height;
		$this->data = $data;
	}

	public static function null() : SerializedImage{
		return new self(0, 0, "");
	}

	public static function fromLegacy(string $skinData) : SerializedImage{
		switch(strlen($skinData)){
			case 0:
				return self::null();
			case 64 * 32 * 4:
				return new SerializedImage(64, 32, $skinData);
			case 64 * 64 * 4:
				return new SerializedImage(64, 64, $skinData);
			case 128 * 64 * 4:
				return new SerializedImage(128, 64, $skinData);
			case 128 * 128 * 4:
				return new SerializedImage(128, 128, $skinData);
		}
		throw new \InvalidArgumentException("Unknown legacy skin size");
	}

	public function getWidth() : int{
		return $this->width;
	}

	public function getHeight() : int{
		return $this->height;
	}

	public function getData() : string{
		return $this->data;
	}
}