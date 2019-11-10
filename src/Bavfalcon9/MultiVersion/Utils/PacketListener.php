<?php

namespace Bavfalcon9\MultiVersion\Utils;

class PacketListener {
    /** @var Int */
    private $networkId;
    /** @var String*/
    private $matchField;
    /** @var Int */
    private $registered;
    /** @var String */
    private $packetName;

    public static $listeners = 0;

    public function __construct(String $packetName, Int $networkId) {
        self::$listeners++;
        $this->registered = self::$listeners;
        $this->networkId = $networkId;
        $this->packetName = $packetName;
    }

    public function getPacketName(): String {
        return $this->packetName;
    }

    public function getPacketNetworkID(): Int {
        return $this->networkId;
    }

    public function getName(): ?String {
        return "Listener$this->registered";
    }

    public static function getAmount(): int {
        return $registered;
    }
}