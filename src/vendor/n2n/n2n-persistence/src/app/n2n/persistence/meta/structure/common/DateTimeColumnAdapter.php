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
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\structure\Column;

use n2n\persistence\meta\structure\DateTimeColumn;

abstract class DateTimeColumnAdapter extends ColumnAdapter implements DateTimeColumn {
	protected $dateAvailable;
	protected $timeAvailable;

	public function __construct($name, $dateAvailable, $timeAvailable) {
		parent::__construct($name);
		$this->dateAvailable = (bool) $dateAvailable;
		$this->timeAvailable = (bool) $timeAvailable;
	}

	public function parseDateTime($rawValue) {
		if (is_null($rawValue)) {
			return null;
		}
		$dateTime = @\DateTime::createFromFormat($this->getParseFormat(), $rawValue);
		if ($dateTime === false && $err = error_get_last()) {
			throw new \InvalidArgumentException($err['message']);
		}

		return $dateTime;
	}

	public function buildRawValue(\DateTime $dateTime = null) {
		if (is_null($dateTime)) {
			return null;
		}

		$rawValue = @$dateTime->format($this->getBuildFormat());

		if ($rawValue === false && $err = error_get_last()) {
			throw new \InvalidArgumentException($err['message']);
		}

		return $rawValue;
	}

	public function isDateAvailable() {
		return $this->dateAvailable;
	}

	public function setDateAvailable($dateAvailable) {
		$this->triggerChangeListeners();
		$this->dateAvailable = $dateAvailable;
	}

	public function isTimeAvailable() {
		return $this->timeAvailable;
	}

	public function setTimeAvailable($timeAvailable) {
		$this->triggerChangeListeners();
		$this->timeAvailable = $timeAvailable;
	}

	public function equalsType(Column $column, $ignoreNull = false) {
		return parent::equalsType($column, $ignoreNull) 
				&& $column instanceof DateTimeColumnAdapter
				&& ($column->isDateAvailable() === $this->isDateAvailable())
				&& ($column->isTimeAvailable() === $this->isTimeAvailable());
	}

	protected abstract function getParseFormat();
	protected abstract function getBuildFormat();
}
