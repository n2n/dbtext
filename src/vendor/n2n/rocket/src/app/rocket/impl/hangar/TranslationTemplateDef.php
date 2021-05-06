<?php
namespace rocket\impl\hangar;

use phpbob\representation\PhpClass;
use n2n\web\dispatch\mag\MagDispatchable;
use phpbob\representation\PhpTypeDef;
use n2n\web\dispatch\mag\MagCollection;
use n2n\l10n\N2nLocale;
use n2n\web\hangar\WebTemplateDef;
use hangar\api\Huo;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\impl\ei\component\prop\translation\Translator;
use n2n\util\StringUtils;
use phpbob\Phpbob;

class TranslationTemplateDef extends WebTemplateDef {
	const PROP_NAME_METHOD_NAME = 'methodName';
	const PROP_NAME_TRANSLATOR_METHOD = 'translatorMethod';
	const PROP_NAME_PROPERTY_NAME = 'propertyName'; 
	const TRANSLATOR_METHOD_REQUIRE = 'require';
	const TRANSLATOR_METHOD_REQUIRE_ANY = 'requireAny';
	const TRANSLATOR_METHOD_FIND = 'find';
	const TRANSLATOR_METHOD_FIND_ANY = 'findAny';
	const TRANSLATOR_METHOD_DEFAULT = self::TRANSLATOR_METHOD_FIND_ANY;
	
	public function getName(): string {
		return 'Translation Container';
	}
	
	public function applyTemplate(Huo $huo, PhpClass $phpClass, MagDispatchable $magDispatchable = null) {
		parent::applyTemplate($huo, $phpClass, $magDispatchable);
		
		self::applyTranslatableValues($phpClass, $magDispatchable);
	}
	
	public function createMagDispatchable(): ?MagDispatchable {
		$magDispatchable = parent::createMagDispatchable();
		
		$magCollection = $magDispatchable->getMagCollection();
		
		self::addTranslabtionMags($magCollection);
		
		return $magDispatchable;
	}
	
	public static function addTranslabtionMags(MagCollection $magCollection) {
		$magCollection->addMag(self::PROP_NAME_METHOD_NAME, new StringMag('Method name', 't'));
		$magCollection->addMag(self::PROP_NAME_PROPERTY_NAME, new StringMag('Property name (default: {entityName}Ts)'));
		$magCollection->addMag(self::PROP_NAME_TRANSLATOR_METHOD,
				new EnumMag('Translator method', array_combine(self::getTranslatorMethods(), self::getTranslatorMethods()), 
				self::TRANSLATOR_METHOD_DEFAULT, true));
		
	}
	
	public static function applyTranslatableValues(PhpClass $phpClass, MagDispatchable $magDispatchable = null) {
		if (null === $magDispatchable) return;
		
		$phpClass->createPhpUse(Translator::class);
		
		$methodName = StringUtils::camelCased($magDispatchable->getPropertyValue(self::PROP_NAME_METHOD_NAME) ?? 't');
		$method = $phpClass->createPhpMethod($methodName);
		$method->createPhpParam('n2nLocales', null, PhpTypeDef::fromTypeName(N2nLocale::class), true);
		
		$propertyName = $magDispatchable->getPropertyValue(self::PROP_NAME_PROPERTY_NAME);
		if (empty($propertyName)) {
			$propertyName = lcfirst($phpClass->getName()) . 'Ts';

		}
		if (StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $propertyName)) {
			$propertyName = substr($propertyName, 1);
		}
		
		$method->setMethodCode("\t\t" . 'return Translator::' . 
				$magDispatchable->getPropertyValue(self::PROP_NAME_TRANSLATOR_METHOD) .  '($this->' . $propertyName . ' , ... $n2nLocales);');
	}
	
	public static function getTranslatorMethods() {
		return [self::TRANSLATOR_METHOD_FIND, self::TRANSLATOR_METHOD_FIND_ANY, self::TRANSLATOR_METHOD_REQUIRE, self::TRANSLATOR_METHOD_REQUIRE_ANY];
	}
}