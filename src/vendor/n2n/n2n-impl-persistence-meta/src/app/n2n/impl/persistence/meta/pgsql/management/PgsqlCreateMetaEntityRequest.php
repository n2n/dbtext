<?php
namespace n2n\impl\persistence\meta\pgsql\management;

use n2n\persistence\Pdo;
use n2n\impl\persistence\meta\pgsql\PgsqlCreateStatementBuilder;
use n2n\persistence\meta\structure\common\CreateMetaEntityRequestAdapter;

class PgsqlCreateMetaEntityRequest extends CreateMetaEntityRequestAdapter {
	public function execute(Pdo $dbh) {
		$createStatementBuilder = new PgsqlCreateStatementBuilder($dbh);
		$createStatementBuilder->setMetaEntity($this->getMetaEntity());
		$createStatementBuilder->executeSqlStatements();
	}
}