<?php
namespace dbtext\test;

use n2n\test\TestEnv;
use n2n\persistence\PdoStatementException;

class GeneralTestEnv  {
	
	static function teardown() {
		if (TestEnv::container()->tm()->hasOpenTransaction()) {
			TestEnv::container()->tm()->getRootTransaction()->rollBack();
		}
	    TestEnv::em()->clear();
		\n2n\test\TestEnv::db()->truncate();

		try {
			\n2n\test\TestEnv::db()->pdo()->exec('DELETE FROM sqlite_sequence');
		} catch (PdoStatementException $e) {
		}

		TestEnv::getN2nContext()->clearLookupInjections();
	}
}
