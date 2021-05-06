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
namespace rocket\impl\ei\component\prop\date;

use n2n\impl\persistence\orm\property\DateTimeEntityProperty;
use n2n\l10n\L10nUtils;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\component\prop\SortableEiProp;
use n2n\core\container\N2nContext;
use rocket\ei\manage\critmod\sort\impl\SimpleSortProp;
use rocket\si\control\SiIconType;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use n2n\util\type\ArgUtils;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\ei\manage\draft\SimpleDraftValueSelection;
use n2n\persistence\meta\OrmDialectConfig;
use rocket\ei\manage\draft\DraftManager;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\manage\draft\PersistDraftAction;
use rocket\ei\EiPropPath;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\util\Eiu;
use n2nutil\jquery\datepicker\mag\DateTimePickerMag;
use n2n\impl\web\ui\view\html\HtmlElement;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\date\conf\DateTimeConfig;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\SiFields;
use n2n\l10n\DateTimeFormat;

class DateTimeEiProp extends DraftablePropertyEiPropAdapter implements SortableEiProp {

	/**
	 * @var DateTimeConfig
	 */
	private $dateTimeConfig;
	
	function __construct() {
		$this->dateTimeConfig = new DateTimeConfig();
	}
	
	function prepare() {
		$this->getConfigurator()->addAdaption($this->dateTimeConfig);
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof DateTimeEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('DateTime', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		$dateTime = $eiu->field()->getValue();
		
		return $eiu->factory()->newGuiField(SiFields::stringOut($dateTime === null ? ''
				: L10nUtils::formatDateTime($dateTime, $eiu->getN2nLocale(), $this->dateTimeConfig->getDateStyle(), $this->dateTimeConfig->getTimeStyle())));
	}
	
	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		$siField = SiFields::dateTimeIn($eiu->field()->getValue())
				->setMandatory($this->getEditConfig()->isMandatory())
				->setDateChoosable($this->dateTimeConfig->getDateStyle() !== DateTimeFormat::STYLE_NONE)
				->setTimeChoosable($this->dateTimeConfig->getTimeStyle() !== DateTimeFormat::STYLE_NONE)
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)->setSaver(function () use ($siField, $eiu) {
			$eiu->field()->setValue($siField->getValue());	
		});
		
// 		$iconElem = new HtmlElement('i', array('class' => SiIconType::ICON_CALENDAR), '');
		
// 		return new DateTimePickerMag($this->getLabelLstr(), $iconElem, $this->getDateStyle(), $this->getTimeStyle(), null, null, 
// 				$this->isMandatory($eiu), array('placeholder' => $this->getLabelLstr(),
// 						'class' => 'form-control rocket-date-picker'));
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			if (null !== ($dateTime = $eiu->object()->readNativValue($this))) {
				return L10nUtils::formatDateTime($dateTime, $n2nLocale, $this->getDateStyle(), $this->getTimeStyle());
			}
			
			return null;
		});
	}
	
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		return new DateTimeDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)),
				$selectDraftStmtBuilder->getPdo()->getMetaData()->getDialect()->getOrmDialectConfig());
	}
	
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder,
			PersistDraftAction $persistDraftAction) {
		ArgUtils::valType($value, 'DateTime', true);
				
		$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), 
				$persistDraftStmtBuilder->getPdo()->getMetaData()->getDialect()->getOrmDialectConfig()
						->buildDateTimeRawValue($value));
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\SortableEiProp::buildSortProp()
	 */
	public function buildSortProp(Eiu $eiu): ?SortProp {
		return new SimpleSortProp(CrIt::p($this->getEntityProperty()), $this->getLabelLstr());
	}
	public function saveSiField(SiField $siField, Eiu $eiu) {
	}

}

class DateTimeDraftValueSelection extends SimpleDraftValueSelection {
	private $ormDialectConfig;
	
	public function __construct($columnAlias, OrmDialectConfig $ormDialectConfig) {
		parent::__construct($columnAlias);
		$this->ormDialectConfig = $ormDialectConfig;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		return $this->ormDialectConfig->parseDateTime($this->rawValue);
	}
}