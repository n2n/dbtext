<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the HANGAR PROJECT.
 *
 * HANGAR is free to use. You are free to redistribute it but are not permitted to make any
 * modifications without the permission of Hofmänner New Media.
 *
 * HANGAR is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * The following people participated in this project:
 *
 * Thomas Günther.............: Developer, Architect, Frontend UI, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Concept
 * Andreas von Burg...........: Concept
 */
namespace hangar\api;

interface ColumnDefaults {
	public function getDefaultIntegerSize(): int;
	public function getDefaultInterSigned(): bool;
	public function getDefaultStringLength(): int;
	public function getDefaultStringCharset(): ?string;
	public function getDefaultTextSize(): int;
	public function getDefaultTextCharset(): ?string;
	public function getDefaultBinarySize(): int;
// 	public function getDefaultDateTimeDateAvailable(): bool;
// 	public function getDefaultDateTimeTimeAvailable(): bool;
	public function getDefaultFixedPointNumIntegerDigits(): int;
	public function getDefaultFixedPointNumDecimalDigits(): int;
	public function getDefaultFloatingPointSize(): int;
}
