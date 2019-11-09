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

use Ahc\Json\Comment as CommentedJsonDecoder;
use InvalidArgumentException;
use function json_encode;

class Skin{
    public const ACCEPTED_SKIN_SIZES = [
        64 * 32 * 4,
        64 * 64 * 4,
        128 * 128 * 4
    ];

    /** @var string */
    private $skinId;
    /** @var string */
    private $skinResourcePatch;
    /** @var SerializedImage */
    private $skinData;
    /** @var SkinAnimation[] */
    private $animations;
    /** @var SerializedImage */
    private $capeData;
    /** @var string */
    private $geometryData;
    /** @var string */
    private $animationData;
    /** @var bool */
    private $premium;
    /** @var bool */
    private $persona;
    /** @var bool */
    private $capeOnClassic;
    /** @var ?string */
    private $capeId;

    public function __construct(string $skinId, string $skinResourcePatch, SerializedImage $skinData, array $animations = [], SerializedImage $capeData = null, string $geometryData = "", string $animationData = "", bool $premium = false, bool $persona = false, $capeOnClassic = false, string $capeId = null){
        $this->skinId = $skinId;
        $this->skinResourcePatch = $skinResourcePatch;
        $this->skinData = $skinData;
        $this->animations = $animations;
        $this->capeData = $capeData;
        $this->geometryData = $geometryData;
        $this->animationData = $animationData;
        $this->premium = $premium;
        $this->persona = $persona;
        $this->capeOnClassic = $capeOnClassic;
        $this->capeId = $capeId;
        $this->debloatGeometryData();
    }

    public static function null() : Skin{
        return new Skin("null", "", SerializedImage::null(), [], SerializedImage::null());
    }

    public static function convertLegacyGeometryName(string $geometryName) : string{
        return '{"geometry" : {"default" : "' . $geometryName . '"}}';
    }

    /**
     * @return string
     */
    public function getSkinResourcePatch() : string{
        return $this->skinResourcePatch;
    }

    /**
     * @return SkinAnimation[]
     */
    public function getAnimations() : array{
        return $this->animations;
    }

    /**
     * @return string
     */
    public function getAnimationData() : string{
        return $this->animationData;
    }

    /**
     * @return bool
     */
    public function isPremium() : bool{
        return $this->premium;
    }

    /**
     * @return bool
     */
    public function isPersona() : bool{
        return $this->persona;
    }

    /**
     * @return bool
     */
    public function isCapeOnClassic() : bool{
        return $this->capeOnClassic;
    }

    public function getCapeId() : string{
        return $this->capeId ?? "";
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isValid() : bool{
        try{
            $this->validate();

            return true;
        }catch(InvalidArgumentException $e){
            return false;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validate() : void{
        if($this->skinId === ""){
            throw new InvalidArgumentException("Skin ID must not be empty");
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
     * @return SerializedImage
     */
    public function getSkinData() : SerializedImage{
        return $this->skinData;
    }

    /**
     * @return SerializedImage
     */
    public function getCapeData() : SerializedImage{
        if($this->capeData === null){
            return new SerializedImage(0, 0, '');
        }

        return $this->capeData;
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

    public function getFullSkinId() : string{
        return $this->skinId . "_" . $this->getCapeId();
    }
}