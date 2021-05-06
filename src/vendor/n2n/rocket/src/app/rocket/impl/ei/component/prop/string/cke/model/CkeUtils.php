<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\string\cke\model;

use n2n\core\container\N2nContext;
use n2n\util\type\ArgUtils;

class CkeUtils {
	
	/**
	 * @param string $ckeLinkProviderLookupId
	 * @param N2nContext $n2nContext
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider|mixed
	 */
	public static function lookupCkeLinkProvider(string $ckeLinkProviderLookupId = null, N2nContext $n2nContext) {
		if ($ckeLinkProviderLookupId === null) return null;
		
		$ckeLinkProvider = null;
		try {
			$ckeLinkProvider = $n2nContext->lookup($ckeLinkProviderLookupId);
		} catch (\n2n\context\LookupFailedException $e) {
			throw new \InvalidArgumentException('Could not lookup CkeLinkProvider with lookup id: ' . $ckeLinkProviderLookupId);
		}
		
		if (!($ckeLinkProvider instanceof CkeLinkProvider)) {
			throw new \InvalidArgumentException('Provided CkeLinkProvider ' . get_class($ckeLinkProvider) 
					. ' does not implement Interface ' . CkeLinkProvider::class);
		}
		
		return $ckeLinkProvider;
	}
	
	/**
	 * @param array $ckeLinkProviderLookupIds
	 * @param N2nContext $n2nContext
	 * @return \rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider[]
	 */
	public static function lookupCkeLinkProviders(array $ckeLinkProviderLookupIds = null, N2nContext $n2nContext) {
		ArgUtils::valArray($ckeLinkProviderLookupIds, 'string', true);
		
		$ckeLinkProviders = array();
		foreach ((array) $ckeLinkProviderLookupIds as $ckeLinkProviderLookupId) {
			$ckeLinkProviders[$ckeLinkProviderLookupId] = self::lookupCkeLinkProvider($ckeLinkProviderLookupId, $n2nContext);
		}
		return $ckeLinkProviders;
	}
	
	/**
	 * @param string $ckeCssConfigLookupId
	 * @param N2nContext $n2nContext
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig
	 */
	public static function lookupCkeCssConfig(string $ckeCssConfigLookupId = null, N2nContext $n2nContext) {
		if ($ckeCssConfigLookupId === null) return null;
		
		$ckeCssConfig = null;
		try {
			$ckeCssConfig = $n2nContext->lookup($ckeCssConfigLookupId);
		} catch (\n2n\context\LookupFailedException $e) {
			throw new \InvalidArgumentException('Could not lookup CkeCssConfig with lookup id: ' 
					. $ckeCssConfigLookupId);
		}
		
		if (!($ckeCssConfig instanceof CkeCssConfig)) {
			throw new \InvalidArgumentException('Provided CkeCssConfig ' . get_class($ckeCssConfig)
					. ' does not implement Interface ' . CkeCssConfig::class);
		}
		
		return $ckeCssConfig;
	}
}

