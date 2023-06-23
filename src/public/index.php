<?php
$pubPath = realpath(dirname(__FILE__));
$appPath = realpath($pubPath . '/../app');
$libPath = realpath($pubPath . '/../lib');
$varPath = realpath($pubPath . '/../var');

set_include_path(implode(PATH_SEPARATOR, array($appPath, $libPath, get_include_path())));

if (isset($_SERVER['N2N_STAGE'])) {
	define('N2N_STAGE', $_SERVER['N2N_STAGE']);
}

require __DIR__ . '/../vendor/autoload.php';
require_once '../vendor/n2n/n2n/src/app/n2n/core/TypeLoader.php';

n2n\core\TypeLoader::register(true,
		require __DIR__ . '/../vendor/composer/autoload_psr4.php',
		require __DIR__ . '/../vendor/composer/autoload_classmap.php');


n2n\core\N2N::initialize($pubPath, $varPath, new n2n\core\FileN2nCache());
//n2n\core\N2N::autoInvokeBatchJobs();
n2n\core\N2N::autoInvokeControllers();
n2n\core\N2N::finalize();


function test($value) {
	if (!n2n\core\N2N::isLiveStageOn()) {
		echo "\r\n<pre>\r\n";
		var_dump($value);
		if (is_scalar($value)) echo "\r\n";
		echo "</pre>\r\n";
	}
}

// n2n\core\N2N::getPdoPool()->getPdo()->getLogger()->dump();