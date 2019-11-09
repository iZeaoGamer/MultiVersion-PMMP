<?php

namespace Bavfalcon9\MultiVersion\Utils;

interface Listener {
    public function onPacketCheck(&$packet): Bool;
    public function onPacketMatch(&$packet): Void;
}