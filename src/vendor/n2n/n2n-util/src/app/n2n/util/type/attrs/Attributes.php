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
namespace n2n\util\type\attrs;

/**
 * @deprecated use {@see DataSet}
 */
class Attributes extends DataSet {
	
	/**
	 * @param string $name
	 * @param bool $nullAllowed
	 * @deprecated use {@see DataSet::reqDataSet()}
	 * @return \n2n\util\type\attrs\Attributes|null
	 */
	public function reqAttributes(string $name, bool $nullAllowed = false) {
		return new Attributes($this->reqArray($name, null, $nullAllowed));
	}
	
	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @deprecated use {@see DataSet::optDataSet()}
	 * @return \n2n\util\type\attrs\Attributes|null
	 */
	public function optAttributes(string $name, $defaultValue = null, bool $nullAllowed = true) {
		if (null !== ($array = $this->optArray($name, null, $defaultValue, $nullAllowed))) {
			return new Attributes($array);
		}
		
		return null;
	}
}