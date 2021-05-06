<?php
$pubPath = realpath(dirname(__FILE__) . '/../public');
$appPath = realpath($pubPath . '/../app');
$libPath = realpath($pubPath . '/../lib');
$varPath = realpath($pubPath . '/../var');
$testPath = realpath(dirname(__FILE__));

set_include_path(implode(PATH_SEPARATOR,
	array($appPath, $libPath, $testPath, get_include_path())));

require __DIR__ . '/../vendor/autoload.php';