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
namespace n2n\impl\persistence\meta\mysql;

use n2n\persistence\meta\structure\common\DateTimeColumnAdapter;

class MysqlDateTimeColumn extends DateTimeColumnAdapter {
	const FORMAT_DATE = 'Y-m-d';
	const FORMAT_DATE_TIME = 'Y-m-d H:i:s';
	const FORMAT_YEAR = 'Y';
	const FORMAT_TIME = 'H:i:s';
		
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->isDateAvailable(), $this->isTimeAvailable());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
	
	protected function getParseFormat() {
		return $this->getFormat();
	}
	
	protected function getBuildFormat() {
		return $this->getFormat();
	}
	
	private function getFormat() {
		if ($this->dateAvailable && $this->timeAvailable) {
			return self::FORMAT_DATE_TIME;
		}
		if ($this->dateAvailable) {
			return self::FORMAT_DATE;
		}
		if ($this->timeAvailable) {
			return self::FORMAT_DATE_TIME;
		}
		return self::FORMAT_YEAR;
	}
}
