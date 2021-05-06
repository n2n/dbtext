<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\impl\persistence\meta\oracle;

class OracleSize {
	//Max Storage of 4000 Bytes = 4000 * 8
	const MAX_SIZE_VARCHAR = 32000;
	
	//Max Storage of 2000 Bytes => 2000 * 8
	const SIZE_RAW = 16000;
	
	//Max Storage of 2GB -1 => 2000000000 * 8
	const SIZE_LONG = 16000000000;
	
	const MAX_FULL_DIGIT_PRECISION = 38;
	
	const MAX_NUMBER_SCALE = 125;
}
