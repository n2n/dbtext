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
namespace n2n\io\orm;

use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\persistence\orm\query\from\MetaTreePoint;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\select\FileSelection;
use n2n\persistence\orm\store\operation\MergeOperation;
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\orm\store\action\PersistAction;
use n2n\persistence\orm\store\action\RemoveAction;
use n2n\impl\persistence\orm\property\EntityPropertyAdapter;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\store\ValueHash;

class FileEntityProperty extends EntityPropertyAdapter {
	private $columnName;
	private $originalFileNameColumnName;
	
	public function __construct(AccessProxy $accessProxy, $columnName, $originalFileNameColumnName) {
		$accessProxy->setConstraint(TypeConstraint::createSimple('n2n\io\managed\File', true));
	
		parent::__construct($accessProxy);
		throw new NotYetImplementedException('Use AnnoManagedFile for ' . $accessProxy->getPropertyName());
		$this->columnName = $columnName;
		$this->originalFileNameColumnName = $originalFileNameColumnName;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::createSelection()
	 */
	public function createSelection(MetaTreePoint $metaTreePoint, QueryState $queryState) {
		return new FileSelection($this->createQueryColumn($metaTreePoint->getMeta()));
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::mergeValue()
	 */
	public function mergeValue($value, $sameEntity, MergeOperation $mergeOperation) {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::supplyPersistAction()
	 */
	public function supplyPersistAction(PersistAction $persistingJob, $value, ValueHash $valueHash, ?ValueHash $oldValueHash) {
		
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::supplyRemoveAction()
	 */
	public function supplyRemoveAction(RemoveAction $removeAction, $value, ValueHash $oldValueHash) {
		
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\property\EntityProperty::createValueHash()
	 */
	public function createValueHash($value, EntityManager $em): ValueHash {
		
	}

	
	
}
