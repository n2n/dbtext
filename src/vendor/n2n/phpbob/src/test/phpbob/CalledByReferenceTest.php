<?php
namespace phpbob;

use PHPUnit\Framework\TestCase;
use phpbob\representation\PhpFile;

class CalledByReferenceTest extends TestCase {
	public function testOne() {
		$phpFile = new PhpFile();
		$phpClass = $phpFile->createPhpClass('CalledByReference');
		$phpClass->createPhpMethod('byReference')->createPhpParam('holeradio')
				->setPassedByReference(true);
		
			$this->assertTrue('<?phpclassCalledByReference{publicfunctionbyReference(&$holeradio){}}' 
							=== preg_replace('/\s/', '', $phpFile->getStringRepresentation()));
		
	}
}