<?php

namespace hangar\api;

use phpbob\representation\PhpClass;
use n2n\web\dispatch\mag\MagDispatchable;

interface HangarTemplateDef {
	public function getName(): string;
	public function applyTemplate(Huo $huo, PhpClass $phpClass, MagDispatchable $magDispatchable = null);
	public function createMagDispatchable(): ?MagDispatchable;
}