<?php
namespace n2n\impl\persistence\meta\pgsql\management;

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\View;
use n2n\persistence\meta\structure\common\DropMetaEntityRequestAdapter;

class PgsqlDropMetaEntityRequest extends DropMetaEntityRequestAdapter {

	public function execute(Pdo $dbh) {
		$metaEntity = $this->getMetaEntity();
		$quotedMetaEntityName = $dbh->quoteField($metaEntity->getName());
		
		$sql = '';
		if ($metaEntity instanceof Table) {
			$sql = 'DROP TABLE IF EXISTS ' . $quotedMetaEntityName . ';';
		} elseif ($metaEntity instanceof View) {
			$sql = 'DROP VIEW IF EXISTS ' . $quotedMetaEntityName . ';';
		}
		
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
	}
}