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
namespace n2n\impl\persistence\meta\mysql;

use n2n\io\InputStream;
use n2n\persistence\meta\data\common\CommonInsertStatementBuilder;
use n2n\persistence\meta\data\common\CommonDeleteStatementBuilder;
use n2n\persistence\meta\data\common\CommonUpdateStatementBuilder;
use n2n\persistence\meta\data\common\CommonSelectStatementBuilder;
use n2n\persistence\meta\structure\InvalidColumnAttributesException;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\Column;
use n2n\core\N2N;
use n2n\persistence\Pdo;
use n2n\impl\persistence\meta\DialectAdapter;
use n2n\persistence\PersistenceUnitConfig;
use n2n\persistence\meta\MetaManager;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\UpdateStatementBuilder;
use n2n\persistence\meta\data\InsertStatementBuilder;
use n2n\persistence\meta\data\DeleteStatementBuilder;
use n2n\persistence\meta\OrmDialectConfig;
use n2n\persistence\meta\data\Importer;

class MysqlDialect extends DialectAdapter {
	/* (non-PHPdoc)
	 * @see \n2n\persistence\meta\Dialect::__construct()
	 */
	public function __construct() {}
	
	public function getName(): string {
		return 'Mysql';
	}
	
	public function initializeConnection(Pdo $dbh, PersistenceUnitConfig $dataSourceConfiguration) {
		$dbh->exec('SET NAMES utf8mb4'); 
		$dbh->exec('SET SESSION TRANSACTION ISOLATION LEVEL ' . $dataSourceConfiguration->getTransactionIsolationLevel());
		$dbh->exec('SET SESSION sql_mode = \'STRICT_ALL_TABLES\'');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createMetaManager()
	 * @return MetaManager
	 */
	public function createMetaManager(Pdo $dbh): MetaManager {
		return new MysqlMetaManager($dbh);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::quoteField()
	 */
	public function quoteField(string $str): string {
		return "`" . str_replace("`", "``", (string) $str) . "`";
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createSelectStatementBuilder()
	 * @return SelectStatementBuilder
	 */
	public function createSelectStatementBuilder(Pdo $dbh): SelectStatementBuilder {
		return new CommonSelectStatementBuilder($dbh, new MysqlQueryFragmentBuilderFactory($dbh));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createUpdateStatementBuilder()
	 * @return UpdateStatementBuilder
	 */
	public function createUpdateStatementBuilder(Pdo $dbh): UpdateStatementBuilder {
		return new CommonUpdateStatementBuilder($dbh, new MysqlQueryFragmentBuilderFactory($dbh));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createInsertStatementBuilder()
	 * @return InsertStatementBuilder
	 */
	public function createInsertStatementBuilder(Pdo $dbh): InsertStatementBuilder {
		return new CommonInsertStatementBuilder($dbh, new MysqlQueryFragmentBuilderFactory($dbh));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createDeleteStatementBuilder()
	 * @return DeleteStatementBuilder
	 */
	public function createDeleteStatementBuilder(Pdo $dbh): DeleteStatementBuilder {
		return new CommonDeleteStatementBuilder($dbh, new MysqlQueryFragmentBuilderFactory($dbh));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::getOrmDialectConfig()
	 * @return OrmDialectConfig
	 */
	public function getOrmDialectConfig(): OrmDialectConfig {
		return new MysqlOrmDialectConfig();
	}

	public function isLastInsertIdSupported(): bool {
		return true;
	}
	
	public function generateSequenceValue(Pdo $dbh, string $sequenceName): ?string {
		return null;
	}
	
	public function applyIdentifierGeneratorToColumn(Pdo $dbh, Column $column, string $sequenceName = null) {
		if (!($column instanceof IntegerColumn)) {
			throw new InvalidColumnAttributesException('Invalid generated identifier column \"' . $column->getName() 
					. '\" for Table \"' . $column->getTable()->getName() 
					. '\". Column must be of type \"' . IntegerColumn::class . "\". Given column type is \"" . get_class($column) . "\"");
		}
		//the Value automatically gets Generated Identifier if the column type is Integer
		//this triggers a changerequest -> type will be changed to INTEGER
		$column->setNullAllowed(false);
		$column->setValueGenerated(true);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Dialect::createImporter()
	 * @return Importer
	 */
	public function createImporter(Pdo $dbh, InputStream $inputStream): Importer {
		return new MysqlImporter($dbh, $inputStream);
	}
}