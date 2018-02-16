<?php
namespace dbtext\config;

use n2n\core\container\N2nContext;
use n2n\core\module\ConfigDescriberAdapter;
use n2n\util\config\Attributes;
use n2n\web\dispatch\mag\MagDispatchable;

class DbTextDescriber extends ConfigDescriberAdapter {
	const ATTR_CREATE_ON_REQUEST_KEY = 'createOnRequest';
	const ATTR_CREATE_ON_REQUEST_DEFAULT = true;

	/**
	 * @see \n2n\core\module\DescriberAdapter::createCustomConfig()
	 *
	 * @return \dbtext\config\DbTextConfig
	 */
	public function buildCustomConfig(): DbTextConfig {
		$attributes = $this->readCustomAttributes();

		$dbConfig = new DbTextConfig();
		$dbConfig->setCreateOnRequest($attributes->getBool(self::ATTR_CREATE_ON_REQUEST_KEY, false,
				self::ATTR_CREATE_ON_REQUEST_DEFAULT));

		return $dbConfig;
	}

	/**
	 * @param N2nContext $n2nContext
	 * @return \n2n\web\dispatch\mag\MagDispatchable
	 */
	public function createMagDispatchable(): MagDispatchable {
		// TODO: Implement createMagDispatchable() method.
	}

	/**
	 * @param Attributes $configAttributes
	 */
	public function saveMagDispatchable(MagDispatchable $magDispatchable) {
		// TODO: Implement saveMagDispatchable() method.
	}
}