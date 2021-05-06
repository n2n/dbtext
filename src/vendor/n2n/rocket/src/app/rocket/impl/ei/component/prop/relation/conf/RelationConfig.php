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
namespace rocket\impl\ei\component\prop\relation\conf;

use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\impl\ei\component\prop\relation\model\RelationVetoableActionListener;
use rocket\ei\EiPropPath;
use rocket\ei\util\spec\EiuEngine;
use n2n\persistence\meta\structure\Column;
use rocket\ei\util\Eiu;
use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;
use rocket\impl\ei\component\prop\relation\command\TargetReadEiCommand;
use rocket\ei\EiCommandPath;
use n2n\l10n\Lstr;
use rocket\impl\ei\component\prop\relation\command\TargetEditEiCommand;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;

class RelationConfig extends PropConfigAdaption {
	const ATTR_TARGET_EXTENSION_ID_KEY = 'targetExtension';
	const ATTR_MIN_KEY = 'min';	// tm
	const ATTR_MAX_KEY = 'max'; // tm
	const ATTR_REMOVABLE_KEY = 'replaceable'; // eto
	const ATTR_REDUCED_KEY = 'reduced'; // emb
	const ATTR_TARGET_REMOVAL_STRATEGY_KEY = 'targetRemovalStrategy';
	const ATTR_TARGET_ORDER_EI_PROP_PATH_KEY = 'targetOrderField'; // etm
	const ATTR_ORPHANS_ALLOWED_KEY = 'orphansAllowed';
	const ATTR_FILTERED_KEY = 'filtered';
	const ATTR_HIDDEN_IF_TARGET_EMPTY_KEY = 'hiddenIfTargetEmpty';
	const ATTR_MAX_PICKS_NUM_KEY = 'maxPicksNum'; // select
	
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	private $displayInOverViewDefault = true;
	
	function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_OVERVIEW_KEY, $this->displayInOverViewDefault);
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->appendAll($magCollection->readValues(array(self::ATTR_TARGET_EXTENSION_ID_KEY,
				self::ATTR_MIN_KEY, self::ATTR_MAX_KEY, self::ATTR_REMOVABLE_KEY, 
				self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY,
				self::ATTR_ORPHANS_ALLOWED_KEY, self::ATTR_FILTERED_KEY, 
				self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, self::ATTR_MAX_PICKS_NUM_KEY, 
				self::ATTR_REDUCED_KEY), true), true);
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		IllegalStateException::assertTrue($this->relationModel !== null, self::class . ' misses RelationModel.');
		
		$lar = new LenientAttributeReader($dataSet);
		
		$targetClass = $this->relationModel->getRelationEntityProperty()->getTargetEntityModel()->getClass();
		$targetEiuType = $eiu->context()->type($targetClass);
		
		$magCollection->addMag(self::ATTR_TARGET_EXTENSION_ID_KEY, 
				new EnumMag('Target Extension', $targetEiuType->getExtensionMaskOptions(), 
						$lar->getString(self::ATTR_TARGET_EXTENSION_ID_KEY), false));
		
		if ($this->relationModel->isTargetMany()) {
			$magCollection->addMag(self::ATTR_MIN_KEY, new NumericMag('Min', $lar->getInt(self::ATTR_MIN_KEY, $this->relationModel->getMin())));
			$magCollection->addMag(self::ATTR_MAX_KEY, new NumericMag('Max', $lar->getInt(self::ATTR_MAX_KEY, $this->relationModel->getMax())));
		}

		if ($this->relationModel->isEmbedded() && $this->relationModel->isTargetMany()
				&& $targetEiuType->mask()->isEngineReady()) {
			$options = $targetEiuType->mask()->engine()->getScalarEiPropertyOptions();
			
			$magCollection->addMag(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY, 
					new EnumMag('Target order field', $options, 
							$lar->getScalar(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY)));
		}
		
		if ($this->relationModel->isEmbedded()) {
			$magCollection->addMag(self::ATTR_REDUCED_KEY, 
					new BoolMag('Reduced', $lar->getBool(self::ATTR_REDUCED_KEY, true)));
		
			$magCollection->addMag(self::ATTR_ORPHANS_ALLOWED_KEY, 
					new BoolMag('Allow orphans', $lar->getBool(self::ATTR_ORPHANS_ALLOWED_KEY, false)));
			
			$magCollection->addMag(self::ATTR_REMOVABLE_KEY,
					new BoolMag('Removable', $lar->getBool(self::ATTR_REMOVABLE_KEY, true)));
		}
		
// 		if (!$this->relationModel->isSourceMany() && $this->relationModel->isSelect()) {
// 			$magCollection->addMag(self::ATTR_FILTERED_KEY, new BoolMag('Filtered',
// 					$lar->getBool(self::ATTR_FILTERED_KEY, true)));
// 		}
		
		if ($this->relationModel->isSelect()) {
			$magCollection->addMag(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, 
					new BoolMag('Hide if target empty', $lar->getBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, true)));

			$magCollection->addMag(self::ATTR_MAX_PICKS_NUM_KEY, 
					new NumericMag('Max Picks', $lar->getInt(self::ATTR_MAX_PICKS_NUM_KEY, 
							$this->relationModel->getMaxPicksNum())));
		}

		if ($this->relationModel->isMaster()) {
			$magCollection->addMag(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, new EnumMag('Target removal startegy', 
					array(RelationVetoableActionListener::STRATEGY_UNSET => 'Unset target',
							RelationVetoableActionListener::STRATEGY_PREVENT => 'Prevent removal',
							RelationVetoableActionListener::STRATEGY_SELF_REMOVE => 'Self remove'),
					$lar->getEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, RelationVetoableActionListener::getStrategies(),
							RelationVetoableActionListener::STRATEGY_UNSET),
					false));
		}
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		IllegalStateException::assertTrue($this->relationModel !== null, self::class . ' misses RelationModel for ' 
				. $this->relationModel->getRelationEntityProperty() . '.');
		
		$targetClass = $this->relationModel->getRelationEntityProperty()->getTargetEntityModel()->getClass();
		$targetEiuType = $eiu->context()->type($targetClass);
		
		if (null !== ($teArr = $dataSet->optScalarArray('targetExtensions', null, true, true))) {
			$dataSet->set(self::ATTR_TARGET_EXTENSION_ID_KEY, ArrayUtils::current($teArr));
		}
		
		$targetExtensionId = $dataSet->optString(self::ATTR_TARGET_EXTENSION_ID_KEY);
		$targetEiuMask = null;
		if ($targetExtensionId !== null) {
			$targetEiuMask = $targetEiuType->extensionMask($targetExtensionId, false);
		} 
		if ($targetEiuMask === null) {
			$targetEiuMask = $targetEiuType->mask();
		}
			
		$targetReadEiCommand = new TargetReadEiCommand(Lstr::create('Embedded Read'), (string) $eiu->mask()->getEiTypePath(),
				(string) $targetEiuMask->getEiTypePath());
		$targetEiuMask->addEiCommand($targetReadEiCommand);
		$this->relationModel->setTargetReadEiCommandPath(EiCommandPath::from($targetReadEiCommand));
		
		$targetEditEiCommand = new TargetEditEiCommand(Lstr::create('Change this name'), (string) $eiu->mask()->getEiTypePath(), 
				(string) $targetEiuMask->getEiTypePath());
		$targetEiuMask->addEiCommand($targetEditEiCommand);
		$this->relationModel->setTargetEditEiCommandPath(EiCommandPath::from($targetEditEiCommand));
		
				
		if ($this->relationModel->isTargetMany()) {
			$this->relationModel->setMin($dataSet->optInt(self::ATTR_MIN_KEY, 
					$this->relationModel->getMin(), false));
			$this->relationModel->setMax($dataSet->optInt(self::ATTR_MAX_KEY, 
					$this->relationModel->getMax(), true));
		}

		if ($this->relationModel->isEmbedded() && $this->relationModel->isTargetMany()) {
			$targetOrderEiPropPath = EiPropPath::build(
					$dataSet->optString(self::ATTR_TARGET_ORDER_EI_PROP_PATH_KEY));
			$targetEiuType->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($targetOrderEiPropPath) {
				if ($targetOrderEiPropPath !== null && $eiuEngine->containsScalarEiProperty($targetOrderEiPropPath)) {
					$this->relationModel->setTargetOrderEiPropPath($targetOrderEiPropPath);
				} else {
					$this->relationModel->setTargetOrderEiPropPath(null);
				}
			});
		}
		
		if ($this->relationModel->isEmbedded()) {
			$this->relationModel->setOrphansAllowed(
					$dataSet->optBool(self::ATTR_ORPHANS_ALLOWED_KEY, $this->relationModel->isOrphansAllowed()));
			
			$this->relationModel->setReduced(
					$dataSet->optBool(self::ATTR_REDUCED_KEY, $this->relationModel->isReduced()));
			
			
			$this->relationModel->setRemovable(
					$dataSet->optBool(self::ATTR_REMOVABLE_KEY, $this->relationModel->isRemovable()));
		}
		
// 		if (!$this->relationModel->isSourceMany() && $this->relationModel->isSelect()) {
// 			$this->relationModel->setFiltered(
// 					$dataSet->optBool(self::ATTR_FILTERED_KEY, $this->relationModel->isFiltered()));
// 		}
		
		if ($this->relationModel->isSelect()) {
			$this->relationModel->setHiddenIfTargetEmpty(
					$dataSet->optBool(self::ATTR_HIDDEN_IF_TARGET_EMPTY_KEY, 
							$this->relationModel->isHiddenIfTargetEmpty()));

			$this->relationModel->setMaxPicksNum($dataSet->optInt(self::ATTR_MAX_PICKS_NUM_KEY,
					$this->relationModel->getMaxPicksNum()));
		}
		
		if ($this->relationModel->isMaster()) {
			$strategy = $dataSet->optEnum(self::ATTR_TARGET_REMOVAL_STRATEGY_KEY, 
					RelationVetoableActionListener::getStrategies(),  
					RelationVetoableActionListener::STRATEGY_UNSET, false);
			
			$targetEiuType->getEiType()->registerVetoableActionListener(
					new RelationVetoableActionListener($this->relationModel, $strategy));		
		}
		
		$this->relationModel->prepare($eiu->mask(), $targetEiuMask);
	}
}
