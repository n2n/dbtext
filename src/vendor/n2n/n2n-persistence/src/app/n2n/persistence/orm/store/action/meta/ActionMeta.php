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
namespace n2n\persistence\orm\store\action\meta;

use n2n\persistence\orm\model\EntityModel;

interface ActionMeta {
	public function getEntityModel();
	/**
	 * @param EntityModel $entityModel
	 * @param string $columnName
	 * @param mixed $rawValue
	 */
	public function setRawValue(EntityModel $entityModel, string $columnName, $rawValue, int $pdoDataType = null);
	/**
	 * @param EntityModel $entityModel
	 * @param string $columnName
	 */
	public function removeRawValue(EntityModel $entityModel, string $columnName);
	/**
	 * @param bool $idGenerated
	 */
	public function setIdGenerated($idGenerated);
	/**
	 * @return boolean 
	 */
	public function isIdGenerated();
	/**
	 * @param string $sequenceName
	 */
	public function setSequenceName($sequenceName);
	/**
	 * @return string 
	 */
	public function getSequenceName();
	/**
	 * @param string $idColumnName
	 */
	public function setIdColumnName($idColumnName);
	/**
	 * @return string 
	 */
	public function getIdColumnName();
	/**
	 * @param mixed $idRawValue
	 * @param bool $assign
	 */
	public function setIdRawValue($idRawValue, bool $assign = false);
	/**
	 * @return mixed 
	 */
	public function getIdRawValue();
	/**
	 * @return mixed 
	 */
	public function getEntityIdRawValue();
	/**
	 * @return \n2n\persistence\orm\store\action\meta\ActionMetaItem[]
	 */
	public function getItems();
	/**
	 * @return boolean
	 */
	public function isEmpty();
}
