<?php

namespace Bavfalcon9\MultiVersion\Protocols;

interface CustomTranslator{

    public function translateCustomPacket(&$packet); // important, i forgot about referencing (use when converting)

}