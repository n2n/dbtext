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
namespace n2n\persistence\orm\model;

class IdenticalNamingStrategy implements NamingStrategy {
	
	public function buildTableName(\ReflectionClass $class, string $tableName = null): string {
		if ($tableName !== null) return $tableName;
	
		return $class->getShortName();
	}
	
	public function buildJunctionTableName(string $ownerTableName, string $propertyName, string $tableName = null): string {
		if ($tableName !== null) return $tableName;
	
		return $ownerTableName . $propertyName;
	}
	
	public function buildColumnName(string $propertyName, string $columnName = null): string {
		if ($columnName !== null) return $columnName;
	
		return $propertyName;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\model\NamingStrategy::buildJunctionJoinColumnName()
	 */
	public function buildJunctionJoinColumnName(\ReflectionClass $targetClass, string $targetIdPropertyName,
			string $joinColumnName = null): string {
		if ($joinColumnName !== null) return $joinColumnName;
	
		return $targetClass->getShortName() . $targetIdPropertyName;
	}
	
	public function buildJoinColumnName(string $propertyName, string $targetIdPropertyName, string $joinColumnName = null): string {
		if ($joinColumnName !== null) return $joinColumnName;
	
		return $propertyName . $targetIdPropertyName;
	}
}
