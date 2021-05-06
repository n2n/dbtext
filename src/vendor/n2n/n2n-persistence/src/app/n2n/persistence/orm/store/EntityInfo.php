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
namespace n2n\persistence\orm\store;

use n2n\persistence\orm\model\EntityModel;

class EntityInfo {
	const STATE_NEW = 'NEW';
	const STATE_MANAGED = 'MANAGED';
	const STATE_REMOVED = 'REMOVED';
	const STATE_DETACHED = 'DETACHED';
	
	private $state;
	private $entityModel;
	private $id;
	/**
	 * @param string $state
	 * @param EntityModel $entityModel
	 * @param mixed $id can be null
	 */
	public function __construct($state, EntityModel $entityModel, $id) {
		$this->state = $state;
		$this->entityModel = $entityModel;
		$this->id = $id;
		
		if ($id === null && (/*$state == self::STATE_MANAGED ||*/ $state == self::STATE_REMOVED)) {
			throw new \InvalidArgumentException('No id defined for Entity: ' 
					. self::buildEntityString($entityModel, $id));
		}
	}
	/**
	 * @return string 
	 */
	public function getState() {
		return $this->state;
	}
	/**
	 * @return EntityModel
	 */
	public function getEntityModel() {
		return $this->entityModel;
	}
	/**
	 * @return boolean
	 */
	public function hasId() {
		return $this->id !== null;
	}
	/**
	 * @return mixed can be null
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function toEntityString() {
		return self::buildEntityString($this->entityModel, $this->id);
	}
	/**
	 * @return string[] 
	 */
	public static function getStates() {
		return array(self::STATE_NEW, self::STATE_MANAGED, self::STATE_REMOVED, self::STATE_DETACHED);
	}
	/**
	 * @param EntityModel $entityModel
	 * @param mixed $id
	 * @return string
	 */
	public static function buildEntityString(EntityModel $entityModel, $id) {
		$idRep = null;
		if ($id !== null) {
			$idRep = $entityModel->getIdDef()->getEntityProperty()->valueToRep($id);
		}
		
		return $entityModel->getClass()->getName() . '#' . ($idRep === null ? '<null>' : $idRep);
	}
	
	public static function buildEntityStringFromEntityObj(EntityModel $entityModel, $entityObj) {
		return self::buildEntityString($entityModel, 
				$entityModel->getIdDef()->getEntityProperty()->readValue($entityObj));
	}
}
