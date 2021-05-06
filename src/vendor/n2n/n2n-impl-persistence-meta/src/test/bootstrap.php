<?php 
use n2n\core\N2N;
use n2n\core\FileN2nCache;
use n2n\core\TypeLoader;

ini_set('display_errors', 1);
error_reporting(E_ALL);

$testPath = realpath(dirname(__FILE__));
$appPath = realpath($testPath . '/../app');
$vendorPath = realpath($testPath . '/../vendor');
$varPath = realpath($testPath . '/../var');

set_include_path(implode(PATH_SEPARATOR, array($appPath, $vendorPath, $testPath, get_include_path())));

define('N2N_STAGE', 'test');
require __DIR__ . '/../vendor/autoload.php';

TypeLoader::register(true,
		require __DIR__ . '/../vendor/composer/autoload_psr4.php',
		require __DIR__ . '/../vendor/composer/autoload_classmap.php');

N2N::initialize('', $varPath, new FileN2nCache());

function test($value) {
	var_dump($value);
}