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
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\Pdo;
use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\meta\structure\common\BackuperAdapter;
use n2n\util\ex\IllegalStateException;

class PgsqlBackuper extends BackuperAdapter {
	const NUM_INSERT_STATEMENTS = 1000;

	public function start() {
		if (!(($this->getOutputStream()) || !($this->getOutputStream()->isOpen()) )) {
			throw new IllegalStateException('Outputstream not set');
		}

		$this->getOutputStream()->write($this->getHeader());

		$metaEntities = $this->getMetaEntities();
		if (count($metaEntities) === 0) {
			$metaEntities = $this->database->getMetaEntities();
		}

		$createStatementBuilder = new PgsqlCreateStatementBuilder($this->dbh);

		foreach ($metaEntities as $metaEntity) {
			if (is_scalar($metaEntity)) {
				$metaEntity = $this->database->getMetaEntityByName($metaEntity);
			}

			if ($this->isBackupStructureEnabled()) {
				$createStatementBuilder->setMetaEntity($metaEntity);
				$this->getOutputStream()->write($createStatementBuilder->toSqlString());
			}

			if ($metaEntity instanceof PgsqlTable && $this->isBackupDataEnabled()) {
				$selectStatementBuilder = $this->dialect->createSelectStatementBuilder($this->dbh);
				$selectStatementBuilder->addFrom(new QueryTable($metaEntity->getName()), null);
				$stmt = $this->dbh->prepare($selectStatementBuilder->toSqlString());
				$stmt->execute();
				$result = $stmt->fetchAll(Pdo::FETCH_ASSOC);

				if (count($result) === 1) {
					foreach ($result as $row) {
						$insertStatementBuilder = $this->dialect->createInsertStatementBuilder($this->dbh);
						$insertStatementBuilder->setTable($metaEntity->getName());
						foreach ($row as $key => $value) {
							$insertStatementBuilder->addColumn(new QueryColumn($key), new QueryConstant($value));
						}
						$this->getOutputStream()->write($insertStatementBuilder->toSqlString());
					}
				} else {
					$insertStatementBuilder = $this->dialect->createInsertStatementBuilder($this->dbh);
					$insertStatementBuilder->setTable($metaEntity->getName());

					foreach ($result as $index => $row) {
						if ($index % self::NUM_INSERT_STATEMENTS == 0) {
							$this->getOutputStream()->write($insertStatementBuilder->toSqlString());
							$insertStatementBuilder = $this->dialect->createInsertStatementBuilder($this->dbh);
							$insertStatementBuilder->setTable($metaEntity->getName());
						}

						$valueGroup = $insertStatementBuilder->createAdditionalValueGroup();
						foreach ($row as $key => $value) {
							$valueGroup->addValue(new QueryConstant($value));
						}
					}
					$this->getOutputStream()->write($insertStatementBuilder->toSqlString());
				}
			}
		}

		$this->getOutputStream()->flush();
	}
}