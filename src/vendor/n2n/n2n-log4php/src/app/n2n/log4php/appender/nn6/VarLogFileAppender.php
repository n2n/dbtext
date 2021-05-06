<?php
namespace n2n\log4php\appender\nn6;
use n2n\log4php\appender\AppenderFile;
use n2n\core\N2N;
use n2n\core\VarStore;

class VarLogFileAppender extends AppenderFile {
	const LOG_FOLDER = 'custom';
	
	protected function getTargetFile() {
		$file = parent::getTargetFile();
		return N2N::getVarStore()->requestFileFsPath(VarStore::CATEGORY_LOG, N2N::NS, 
				self::LOG_FOLDER, $file, true, true);
	}
}