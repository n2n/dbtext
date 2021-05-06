<?php
namespace rocket\ei\component\prop;

use rocket\ei\util\Eiu;
use rocket\ei\manage\security\filter\SecurityFilterProp;

interface SecurityFilterEiProp extends EiProp {
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp|null
	 */
	public function buildSecurityFilterProp(Eiu $eiu): ?SecurityFilterProp;
}