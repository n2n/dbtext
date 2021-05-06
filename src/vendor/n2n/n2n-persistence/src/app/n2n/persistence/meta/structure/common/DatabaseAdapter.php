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

use n2n\persistence\meta\structure\DuplicateMetaElementException;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\UnknownMetaEntityException;
use n2n\persistence\meta\Database;
use n2n\util\type\CastUtils;
use n2n\util\type\ArgUtils;

abstract class DatabaseAdapter implements Database, MetaEntityChangeListener {

	private $name;
	private $charset;
	/**
	 * @var MetaEntity []
	 */
	private $metaEntities = [];
	private $attrs;
	/**
	 * @var DatabaseChangeListener [];
	 */
	private $changeListeners = [];
	
	public function __construct(string $name, string $charset, array $metaEntities, array $attrs) {
		$this->name = $name;
		$this->charset = $charset;
		$this->attrs = $attrs;
		
		foreach ($metaEntities as $metaEntity) {
			$this->addMetaEntity($metaEntity);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::getName()
	 */
	public function getName(): string {
		return $this->name;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::getCharset()
	 */
	public function getCharset(): string {
		return $this->charset;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::getAttrs()
	 */
	public function getAttrs(): array {
		return $this->attrs;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::getMetaEntities()
	 */
	public function getMetaEntities(): array {
		return $this->metaEntities;
	}
	
	public function getMetaEntityByName(string $name): MetaEntity {
		foreach ($this->metaEntities as $metaEntity) {
			if ($metaEntity->getName() === $name) return $metaEntity;
		}
		
		throw new UnknownMetaEntityException('Metaentity "' . $name . '" does not exist in Database "' 
				. $this->getName() . '" ');
	}

	public function containsMetaEntityName(string $name): bool {
		try {
			$this->getMetaEntityByName($name);
			return true;
		} catch (UnknownMetaEntityException $e) {
			return false;
		}
	}

	public function setMetaEntities(array $metaEntities) {
		//Find out the metaEntities which need to be deleted
		foreach ($this->metaEntities as $metaEntity) {
			if (!(in_array($metaEntity, $metaEntities))) {
				$this->removeMetaEntity($metaEntity);
			}
		}

		//Find out the ones who have to be added
		foreach ($metaEntities as $metaEntity) {
			if (!(in_array($metaEntity, $this->metaEntities))) {
				$this->addMetaEntity($metaEntity);
			}
		}
	}

	public function removeMetaEntityByName(string $name) {
		$this->removeMetaEntity($this->getMetaEntityByName($name));
	}

	public function addMetaEntity(MetaEntity $metaEntity) {
		if ($this->containsMetaEntityName($metaEntity->getName())) {
			if (!($metaEntity->equals($this->getMetaEntityByName($metaEntity->getName())))) {
				throw new DuplicateMetaElementException('Duplicate meta entity "' . $metaEntity->getName() 
						. '" in Database "' . $this->getName() . '"');
			}
			return;
		}
		
		CastUtils::assertTrue($metaEntity instanceof MetaEntityAdapter);
		$metaEntity->registerChangeListener($this);
		$metaEntity->setDatabase($this);
		$this->metaEntities[] = $metaEntity;
		$this->triggerOnMetaEntityCreate($metaEntity);
	}

	public function onMetaEntityChange(MetaEntity $metaEntity) {
		$this->triggerOnMetaEntityAlter($metaEntity);
	}

	public function onMetaEntityNameChange(string $orginalName, MetaEntity $metaEntity) {
		$this->triggerOnMetaEntityNameChange($orginalName, $metaEntity);
	}

	private function removeMetaEntity(MetaEntity $metaEntity) {
		if (!$this->containsMetaEntityName($metaEntity->getName())) return;
		
		ArgUtils::assertTrue($metaEntity instanceof MetaEntityAdapter);
		$this->metaEntities = array_filter($this->metaEntities, function(MetaEntity $aMetaEntity) use ($metaEntity) {
			return !$metaEntity->equals($aMetaEntity);
		});
		
		$this->triggerOnMetaEntityDrop($metaEntity);
	}
	
	
	public function registerChangeListener(DatabaseChangeListener $changeListener) {
		$this->changeListeners[spl_object_hash($changeListener)] = $changeListener;
	}
	
	public function unregisterChangeListener(DatabaseChangeListener $changeListener) {
		unset($this->changeListeners[spl_object_hash($changeListener)]);
	}
	
	protected function triggerOnMetaEntityAlter(MetaEntity $metaEntity) {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityAlter($metaEntity);
		}
	}
	
	protected function triggerOnMetaEntityCreate(MetaEntity $metaEntity) {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityCreate($metaEntity);
		}
	}
	
	protected function triggerOnMetaEntityDrop(MetaEntity $metaEntity) {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityDrop($metaEntity);
		}
	}
	
	protected function triggerOnMetaEntityNameChange(string $originalName, MetaEntity $metaEntity) {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityNameChange($originalName, $metaEntity);
		}
	}
}
