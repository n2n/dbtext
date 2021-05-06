// <?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\ei\adaptive;

// use n2n\persistence\orm\model\EntityModel;
// use n2n\persistence\orm\criteria\querypoint\MetaGenerator;
// use n2n\persistence\meta\data\SelectStatementBuilder;
// use n2n\persistence\meta\data\QueryComparator;
// use n2n\persistence\orm\query\from\meta\TreePointMeta;

// class AdaptiveTreePointMeta implements TreePointMeta {
// 	private $decoratedTreePointMeta;
// 	private $columnNamePrefix;
	
// 	public function __construct(TreePointMeta $decoratedTreePointMeta, $tableNamePrefix, $columnNamePrefix, $idColumnName) {
// 		$this->decoratedTreePointMeta = $decoratedTreePointMeta;
// 		$this->columnNamePrefix = $columnNamePrefix;
		
// 		$decoratedTreePointMeta->setIdColumnName($idColumnName);
// 	}
	
// 	public function registerMetaColumn($columnName) {
// 		return $this->decoratedTreePointMeta->registerColumn($this->decoratedTreePointMeta->getEntityModel()->getTopEntityModel(), $columnName);
// 	}
	
// 	public function getMetaQueryColumnByName($columnName) {
// 		return $this->decoratedTreePointMeta->getQueryColumnByName($this->decoratedTreePointMeta->getEntityModel()->getTopEntityModel(), $columnName);
// 	}
	
// 	public function getMetaColumnAliases() {
// 		return $this->decoratedTreePointMeta->getMetaColumnAliases();
// 	}
	
// 	public function registerColumn(EntityModel $entityModel, $columnName) {
// 		return $this->decoratedTreePointMeta->registerColumn($entityModel, $this->columnNamePrefix . $columnName);
// 	}
	
// 	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {
// 		return $this->decoratedTreePointMeta->getQueryColumnByName($entityModel, $this->columnNamePrefix . $columnName);
// 	}
	
// 	public function applyAsFrom(SelectStatementBuilder $selectStatementBuilder) {
// 		$this->decoratedTreePointMeta->applyAsFrom($selectStatementBuilder);
// 	}
	
// 	public function applyAsJoin(SelectStatementBuilder $selectStatementBuilder, $joinType, 
// 			QueryComparator $onComparator = null) {
// 		$this->decoratedTreePointMeta->applyAsJoin($selectStatementBuilder, $joinType, $onComparator);
// 	}
	
// 	public function makeIdentifiable() {
// 		$this->decoratedTreePointMeta->makeIdentifiable();
// 	}
	
// 	public function identifyEntityModel(array $result) {
// 		return $this->decoratedTreePointMeta->identifyEntityModel($result);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::getEntityModel()
// 	 */
// 	public function getEntityModel() {
// 		return $this->decoratedTreePointMeta->getEntityModel();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::setIdColumnName()
// 	 */
// 	public function setIdColumnName($idColumnname) {
// 		$this->decoratedTreePointMeta->setIdColumnName($idColumnname);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\criteria\querypoint\QueryPoint::setMetaGenerator()
// 	 */
// 	public function setMetaGenerator(MetaGenerator $metaGenerator = null) {
// 		$this->decoratedTreePointMeta->setMetaGenerator($metaGenerator);
// 	}	
// }
