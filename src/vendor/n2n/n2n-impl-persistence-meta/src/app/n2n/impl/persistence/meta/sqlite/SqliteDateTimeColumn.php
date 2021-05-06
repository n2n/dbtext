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
namespace n2n\impl\persistence\meta\sqlite;

use n2n\persistence\meta\structure\common\ColumnAdapter;

use n2n\persistence\meta\structure\DateTimeColumn;

class SqliteDateTimeColumn extends ColumnAdapter implements DateTimeColumn {
	
	const FORMAT_DATE_TIME = 'Y-m-d H:i:s';
	const COLUMN_TYPE_NAME = 'N2N_DATE_TIME';
	
	public function isDateAvailable() {
		return true;
	}
	
	public function isTimeAvailable() {
		return true;
	}
	
	public function parseDateTime($rawValue) {
		if (is_null($rawValue)) {
			return null;
		}
		$dateTime = @\DateTime::createFromFormat(self::FORMAT_DATE_TIME, $rawValue);
		if ($dateTime === false && $err = error_get_last()) {
			throw new \InvalidArgumentException($err['message']);
		}
		
		return $dateTime;
	}

	public function buildRawValue(\DateTime $dateTime = null) {
		if (is_null($dateTime)) {
			return null;
		}
		
		$rawValue = @$dateTime->format(self::FORMAT_DATE_TIME);
		
		if ($rawValue === false && $err = error_get_last()) {
			throw new \InvalidArgumentException($err['message']);
		}
		
		return $rawValue;
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName);
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
}
