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
namespace n2n\util\type;

class TypeConstraints {
	
	/**
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function scalar(bool $nullable = false) {
		return TypeConstraint::createSimple('scalar', $nullable);
	}
	
	/**
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function string(bool $nullable = false) {
		return TypeConstraint::createSimple('string', $nullable);
	}
	
	/**
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function int(bool $nullable = false) {
		return TypeConstraint::createSimple('int', $nullable);
	}
	
	/**
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function float(bool $nullable = false) {
		return TypeConstraint::createSimple('float', $nullable);
	}
	
	/**
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function mixed(bool $nullable = false) {
		return TypeConstraint::createSimple(TypeName::PSEUDO_MIXED, $nullable);
	}
	
	/**
	 * @param string $name
	 * @param bool $nullable
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function type($name, bool $nullable = false) {
		return TypeConstraint::createSimple($name, $nullable);
	}
	
	/**
	 * @param bool $nullable
	 * @param TypeConstraint|string $fieldTypeConstraint
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function array(bool $nullable = false, $fieldTypeConstraint = null) {
		return TypeConstraint::createArrayLike('array', $nullable, TypeConstraint::build($fieldTypeConstraint));
	}
	
	/**
	 * @param bool $nullable
	 * @param TypeConstraint|string $fieldTypeConstraint
	 * @return \n2n\util\type\TypeConstraint
	 */
	static function arrayObject(bool $nullable, $fieldTypeConstraint = null) {
		return TypeConstraint::createArrayLike('ArrayObject', $nullable, TypeConstraint::build($fieldTypeConstraint));
	}
}