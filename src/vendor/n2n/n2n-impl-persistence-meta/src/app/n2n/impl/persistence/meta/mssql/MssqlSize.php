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
namespace n2n\impl\persistence\meta\mssql;

class MssqlSize {
	// @see http://msdn.microsoft.com/en-us/library/ms186939.aspx
	const MAX_STRING_SETTABLE_LENGTH = 4000;
	//(2 ^ 31 - 1) / 2 = 1'073'741'823
	const MAX_STRING_STORAGE_LENGTH = 1073741823;
	
	//@see http://msdn.microsoft.com/en-us/library/ms176089.aspx
	//8000 Bytes => 8000 * 8 Bits
	const MAX_TEXT_SETTABLE_SIZE = 64000;
	//2^31 -1 Bytes => 2147483647 * 8 Bits
	const MAX_TEXT_STORAGE_SIZE = 17179869176;
}
