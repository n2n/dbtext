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
namespace n2n\persistence\meta;

use n2n\io\InputStream;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\PersistenceUnitConfig;
use n2n\persistence\Pdo;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\UpdateStatementBuilder;
use n2n\persistence\meta\data\InsertStatementBuilder;
use n2n\persistence\meta\data\DeleteStatementBuilder;
use n2n\persistence\meta\data\Importer;

interface Dialect {
	const DEFAULT_ESCAPING_CHARACTER = '\\';
	/**
	 * 
	 */
	public function __construct();
	/**
	 * @return string
	 */
	public function getName(): string;
	/**
	 * @param Pdo $dbh
	 * @param PersistenceUnitConfig $data
	 */
	public function initializeConnection(Pdo $dbh, PersistenceUnitConfig $data);
	/**
	 * @param Pdo $dbh
	 * @return MetaManager
	 */
	public function createMetaManager(Pdo $dbh): MetaManager;
	/**
	 * @param string $str
	 */
	public function quoteField(string $str): string;
	/**
	 * Quotes the like wildcard chars
	 * @param string $pattern
	 */
	public function escapeLikePattern(string $pattern): string;
	/**
	 * Returns the escape character used in {@link Dialect::escapeLikePattern()}. 
	 * @return string
	 */
	public function getLikeEscapeCharacter(): string;
	/**
	 * @param Pdo $dbh
	 * @return SelectStatementBuilder
	 */
	public function createSelectStatementBuilder(Pdo $dbh): SelectStatementBuilder;
	/**
	 * @param Pdo $dbh
	 * @return UpdateStatementBuilder
	 */
	public function createUpdateStatementBuilder(Pdo $dbh): UpdateStatementBuilder;
	/**
	 * @param Pdo $dbh
	 * @return InsertStatementBuilder
	 */
	public function createInsertStatementBuilder(Pdo $dbh): InsertStatementBuilder;
	/**
	 * 
	 * @param Pdo $dbh
	 * @return DeleteStatementBuilder
	 */
	public function createDeleteStatementBuilder(Pdo $dbh): DeleteStatementBuilder;
	/**
	 * @param Pdo $dbh
	 * @param InputStream $inputStream
	 * @return Importer
	 */
	public function createImporter(Pdo $dbh, InputStream $inputStream): Importer;
	/**
	 * @return \n2n\persistence\meta\OrmDialectConfig
	 */
	public function getOrmDialectConfig(): OrmDialectConfig;
	/**
	 * @return bool
	 */
	public function isLastInsertIdSupported(): bool;
	/**
	 * @param string $sequenceName
	 * @return string|null
	 */
	public function generateSequenceValue(Pdo $dbh, string $sequenceName): ?string;
	/**
	 * @param Column $column
	 */
	public function applyIdentifierGeneratorToColumn(Pdo $dbh, Column $column, string $sequenceName);
}