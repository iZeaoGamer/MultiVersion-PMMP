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

namespace Bavfalcon9\MultiVersion;

use pocketmine\plugin\PluginBase;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use Bavfalcon9\MultiVersion\Utils\ProtocolVersion;

class Main extends PluginBase {
    public $EventManager;
    public $server_version;

    public function onEnable() {
        $this->server_version = ProtocolInfo::MINECRAFT_VERSION_NETWORK;
        if (!isset(ProtocolVersion::VERSIONS[$this->server_version])) {
            $this->getLogger()->critical("Server version:". $this->server_version . "not supported by multiversion.");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        define('MultiVersionFile', $this->getFile());
        $this->EventManager = new EventManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this->EventManager, $this);
        $this->saveAllResources();
    }

    private function saveAllResources() {
        $resourcePath = $this->getFile() . "resources";
        $versions = scandir($resourcePath);

        foreach ($versions as $version) {
            if ($version === '.' || $version === '..') {
                continue;
            } else {
                $files = scandir($resourcePath . "/" . $version);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    $this->saveResource($version . "/" . $file);
                }
            }
        }
    }
}