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
namespace n2n\persistence\orm\query\from\meta;

use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\query\QueryState;

interface TreePointMeta {
	
	public function registerColumn(EntityModel $entityModel, $columnName);

	public function getQueryColumnByName(EntityModel $entityModel, $columnName);

	public function applyAsFrom(SelectStatementBuilder $selectStatementBuilder);

	public function applyAsJoin(SelectStatementBuilder $selectStatementBuilder, $joinType, QueryComparator $onComparator = null);

	public function getEntityModel(): EntityModel;

	public function setIdColumnName(string $idColumnname);

	public function setMetaGenerator(MetaGenerator $metaGenerator = null);
	
	public function getMetaColumnAliases();
	/**
	 * @return \n2n\persistence\orm\query\select\Selection
	 */
	public function createDiscriminatorSelection();
	/**
	 * @param QueryState $queryState
	 * @return \n2n\persistence\orm\criteria\compare\ComparisonStrategy
	 */
	public function createDiscriminatorComparisonStrategy(QueryState $queryState);
}
