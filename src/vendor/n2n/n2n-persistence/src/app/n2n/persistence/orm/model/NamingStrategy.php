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

interface NamingStrategy {
	/**
	 * @param \ReflectionClass $class
	 * @param string $tableName
	 * @return string
	 */
	public function buildTableName(\ReflectionClass $class, string $tableName = null): string;
	/**
	 * @param \ReflectionClass $class1
	 * @param \ReflectionClass $class2
	 * @param string $tableName
	 * @return string
	 */
	public function buildJunctionTableName(string $ownerTableName, string $propertyName, string $tableName = null): string;
	/**
	 * @param string $propertyName
	 * @param string $columnName
	 * @return string
	 */
	public function buildColumnName(string $propertyName, string $columnName = null): string;
	/**
	 * @param \ReflectionClass $targetClass
	 * @param string $targetIdPropertyName
	 * @return string
	 */
	public function buildJunctionJoinColumnName(\ReflectionClass $targetClass, string $targetIdPropertyName, 
			string $joinColumnName = null): string;
	/**
	 * @param string $propertyName
	 * @param string $targetIdPropertyName
	 * @param string $joinColumnName
	 * @return string
	 */
	public function buildJoinColumnName(string $propertyName, string $targetIdPropertyName, 
			string $joinColumnName = null): string;
}
