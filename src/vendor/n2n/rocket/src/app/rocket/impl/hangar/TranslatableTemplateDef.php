<?php
namespace rocket\impl\hangar;

use hangar\api\HangarTemplateDef;
use phpbob\representation\PhpClass;
use n2n\web\dispatch\mag\MagDispatchable;
use phpbob\representation\PhpTypeDef;
use n2n\reflection\ObjectAdapter;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\l10n\N2nLocale;
use rocket\impl\ei\component\prop\translation\Translatable;
use n2n\web\hangar\WebTemplateDef;
use hangar\api\Huo;

class TranslatableTemplateDef implements HangarTemplateDef {
	
	const PROP_NAME_APPLY_OBJECT_ADAPTER = 'applyObjectAdapter';
	
	public function getName(): string {
		return 'Translatable';
	}
	
	public function applyTemplate(Huo $huo, PhpClass $phpClass, MagDispatchable $magDispatchable = null) {
		$phpClass->addInterfacePhpTypeDef(PhpTypeDef::fromTypeName(Translatable::class));
		$phpClass->createPhpProperty('id');
		$phpClass->createPhpGetterAndSetter('id', new PhpTypeDef('int'));
		
		$phpClass->createPhpProperty('n2nLocale');
		$phpClass->createPhpGetterAndSetter('n2nLocale', PhpTypeDef::fromTypeName(N2nLocale::class));
		
		if ($magDispatchable->getPropertyValue(self::PROP_NAME_APPLY_OBJECT_ADAPTER)) {
			$phpClass->setSuperClassTypeDef(PhpTypeDef::fromTypeName(ObjectAdapter::class));
		}
		
		WebTemplateDef::applyResponseCacheClearerValue($phpClass, $magDispatchable);
	}
	
	public function createMagDispatchable(): ?MagDispatchable {
		$magCollection = new MagCollection();
		
		$magCollection->addMag(self::PROP_NAME_APPLY_OBJECT_ADAPTER,
				new BoolMag('Apply object adapter', true));
		WebTemplateDef::addResponseCacheClearerMag($magCollection);
		
		return new MagForm($magCollection);
	}
}