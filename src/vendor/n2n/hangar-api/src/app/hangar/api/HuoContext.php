<?php
namespace hangar\api;

use n2n\core\container\N2nContext;
use n2n\persistence\orm\model\EntityModelManager;

interface HuoContext {
	/**
	 * @return N2nContext
	 */
	public function getN2nContext(): N2nContext;
	/**
	 * @param bool $reload
	 * @return EntityModelManager
	 */
	public function getEntityModelManager(bool $reload = false): EntityModelManager;
}