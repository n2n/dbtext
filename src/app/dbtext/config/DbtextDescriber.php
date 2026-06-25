<?php
namespace dbtext\config;

use n2n\core\module\ConfigDescriberAdapter;
use n2n\web\dispatch\mag\MagDispatchable;

class DbtextDescriber extends ConfigDescriberAdapter {
	const ATTR_CREATE_ON_REQUEST_KEY = 'createOnRequest';
	const ATTR_CREATE_ON_REQUEST_DEFAULT = true;

	/**
	 * @return \dbtext\config\DbtextConfig
	 */
	public function buildCustomConfig(): DbtextConfig {
		$dataSet = $this->readCustomAttributes();

		$dbConfig = new DbtextConfig();
		$dbConfig->setModifyOnRequest($dataSet->optBool(self::ATTR_CREATE_ON_REQUEST_KEY, 
				self::ATTR_CREATE_ON_REQUEST_DEFAULT));

		return $dbConfig;
	}

	public function createMagDispatchable(): MagDispatchable {
		// TODO: Implement createMagDispatchable() method.
	}

	public function saveMagDispatchable(MagDispatchable $magDispatchable) {
		// TODO: Implement saveMagDispatchable() method.
	}
}