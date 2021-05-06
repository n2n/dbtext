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
namespace rocket\spec;

use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use rocket\ei\EiType;
use n2n\util\type\attrs\DataSet;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use rocket\ei\component\EiConfigurator;
use rocket\ei\mask\EiMask;
use n2n\persistence\orm\OrmConfigurationException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\config\InvalidConfigurationException;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\command\IndependentEiCommand;
use rocket\ei\component\modificator\IndependentEiModificator;
use rocket\spec\extr\EiTypeExtraction;
use rocket\spec\extr\EiMaskExtraction;
use rocket\spec\extr\EiPropExtraction;
use rocket\spec\extr\EiComponentExtraction;
use rocket\spec\extr\EiTypeExtensionExtraction;
use rocket\spec\extr\EiModificatorExtraction;
use rocket\ei\EiTypeExtension;
use n2n\util\StringUtils;
use rocket\core\model\Rocket;
use rocket\ei\component\prop\EiPropCollection;
use rocket\ei\EiPropPath;
use rocket\spec\result\EiErrorResult;
use rocket\ei\EiCommandPath;
use rocket\spec\result\EiCommandError;
use rocket\spec\result\EiModificatorError;
use rocket\ei\EiModificatorPath;
use rocket\spec\result\EiPropError;

class EiTypeFactory {
	private $entityModelManager;
	private $setupQueue;
	private $eiErrorResult;
	
	public function __construct(EntityModelManager $entityModelManager, ?EiSetupQueue $setupQueue, 
			?EiErrorResult $eiErrorResult) {
		$this->entityModelManager = $entityModelManager;
		$this->setupQueue = $setupQueue;
		$this->eiErrorResult = $eiErrorResult;
	}
	/**
	 * @param EiTypeExtraction $eiTypeExtraction
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 * @param EiTypeExtensionExtraction[] $eiTypeExtensionExtractions
	 * @throws InvalidConfigurationException
	 * @return \rocket\ei\EiType
	 */
	public function create(EiTypeExtraction $eiTypeExtraction, array $eiModificatorExtractions) {
		$eiType = null;
		try {
			$eiType = new EiType($eiTypeExtraction->getId(), $eiTypeExtraction->getModuleNamespace());
			$this->asdf($eiTypeExtraction->getEiMaskExtraction(), $eiType->getEiMask(), $eiModificatorExtractions);
		} catch (InvalidConfigurationException $e) {
			throw $this->createEiTypeException($eiTypeExtraction->getId(), $e);
		}
		
		$eiType->setDataSourceName($eiTypeExtraction->getDataSourceName());
		$eiType->setNestedSetStrategy($eiTypeExtraction->getNestedSetStrategy());
		$eiType->setEntityModel($this->getEntityModel($eiTypeExtraction->getEntityClassName()));
		
// 		$eiTypeExtensionCollection = $eiType->getEiTypeExtensionCollection();
// 		foreach ($eiTypeExtraction->getEiTypeExtensionExtractions() as $eiTypeExtensionExtraction) {
// 			try {
// 				$eiTypeExtensionCollection->add($this->createEiTypeExtension($eiType, $eiTypeExtensionExtraction));
// 			} catch (InvalidConfigurationException $e) {
// 				throw $this->createEiTypeException($eiTypeExtraction->getId(),
// 						$this->createEiMaskException($eiTypeExtensionExtraction->getId(), $e));
// 			}
// 		}
		
		return $eiType;
	}
	
	
	
	private function getEntityModel($entityClassName) {
		try {
			return $this->entityModelManager->getEntityModelByClass(
					ReflectionUtils::createReflectionClass($entityClassName));
		} catch (TypeNotFoundException $e) {
			throw new InvalidSpecConfigurationException('EiType is defined for unknown entity: ' . $entityClassName, 0, $e);
		} catch (OrmConfigurationException $e) {
			throw new InvalidSpecConfigurationException('EiType is defined for invalid entity: ' . $entityClassName, 0, $e);
		}
	}
	
	/**
	 * @param EiMaskExtraction $eiMaskExtraction
	 * @param EiMask $eiMask
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 */
	private function asdf(EiMaskExtraction $eiMaskExtraction, EiMask $eiMask, array $eiModificatorExtractions) {
		$eiTypePath = $eiMask->getEiTypePath();
		
		$eiDef = $eiMask->getDef();
		$eiDef->setLabel($eiMaskExtraction->getLabel());
		$eiDef->setPluralLabel($eiMaskExtraction->getPluralLabel());
		$eiDef->setIconType($eiMaskExtraction->getIconType());
		$eiDef->setIdentityStringPattern($eiMaskExtraction->getIdentityStringPattern());
		
		if (null !== ($draftingAllowed = $eiMaskExtraction->isDraftingAllowed())) {
			$eiDef->setDraftingAllowed($draftingAllowed);
		}
		$eiDef->setPreviewControllerLookupId($eiMaskExtraction->getPreviewControllerLookupId());
		
		$eiPropCollection = $eiMask->getEiPropCollection();
		$this->applyEiProps($eiMaskExtraction->getEiPropExtractions(), $eiPropCollection);
// 		foreach ($eiMaskExtraction->getEiPropExtractions() as $eiPropExtraction) {
// 			try {
// 				$eiPropWrapper = $eiPropCollection->addIndependent($eiPropExtraction->getId(), 
// 						$this->createEiProp($eiPropExtraction, $eiMask));
// 				$this->applyEiProps($ei);
// 			} catch (TypeNotFoundException $e) {
// 				throw $this->createEiPropException($eiPropExtraction, $e);
// 			} catch (InvalidConfigurationException $e) {
// 				throw $this->createEiPropException($eiPropExtraction, $e);
// 			}
// 		}

		$eiCommandCollection = $eiMask->getEiCommandCollection();
		foreach ($eiMaskExtraction->getEiCommandExtractions() as $eiComponentExtraction) {
			try {
				try {
					$eiCommandCollection->addIndependent($eiComponentExtraction->getId(),
							$this->createEiCommand($eiComponentExtraction, $eiMask));
				} catch (TypeNotFoundException $e) {
					throw $this->createEiCommandException($eiComponentExtraction->getId(), $e);
				} catch (InvalidConfigurationException $e) {
					throw $this->createEiCommandException($eiComponentExtraction->getId(), $e);
				}
			} catch (\Throwable $t) {
				if (null === $this->eiErrorResult) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiCommandError(
						new EiCommandError($eiTypePath, EiCommandPath::create($eiComponentExtraction->getId()), $t));
			}
		}
		
		$eiDef->setFilterSettingGroup($eiMaskExtraction->getFilterSettingGroup());
		$eiDef->setDefaultSortSettingGroup($eiMaskExtraction->getDefaultSortSettingGroup());
		
		$eiModificatorCollection = $eiMask->getEiModificatorCollection();
		foreach ($eiModificatorExtractions as $eiModificatorExtraction) {
			try {
				try {
					$eiModificatorCollection->addIndependent($eiModificatorExtraction->getId(),
							$this->createEiModificator($eiModificatorExtraction, $eiMask));
				} catch (InvalidConfigurationException $e) {
					throw $this->createEiModificatorException($eiModificatorExtraction->getId(), $e);
				}
			} catch (\Throwable $t) {
				if (null === $this->eiErrorResult) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiModificatorError(
						new EiModificatorError($eiTypePath, EiModificatorPath::create($eiModificatorExtraction->getId()), $t));
			}
		}
		
		$eiMask->setDisplayScheme($eiMaskExtraction->getDisplayScheme());
	}
	
	/**
	 * @param EiPropExtraction[] $eiPropExtractions
	 */
	private function applyEiProps(array $eiPropExtractions, EiPropCollection $eiPropCollection, array $contextEntityPropertyNames = array(), 
			EiPropPath $contextEiPropPath = null) {
		foreach ($eiPropExtractions as $eiPropExtraction) {
			try {
				$eiPropWrapper = null;
				try {
					$eiPropWrapper = $eiPropCollection->addIndependent(
							$eiPropExtraction->getId(),
							$this->createEiProp($eiPropExtraction, $eiPropCollection->getEiMask(), $contextEntityPropertyNames), 
							$contextEiPropPath);
				} catch (TypeNotFoundException $e) {
					throw $this->createEiPropException($eiPropExtraction, $e);
				} catch (InvalidConfigurationException $e) {
					throw $this->createEiPropException($eiPropExtraction, $e);
				}
				
				$forkedEiPropExtractions = $eiPropExtraction->getForkedEiPropExtractions();
				if (empty($forkedEiPropExtractions)) {
					continue;
				}
				
				$entityPropertyNames = $contextEntityPropertyNames;
				if (null !== ($epn = $eiPropExtraction->getEntityPropertyName())) {
					$entityPropertyNames[] = $epn;
				} else {
					throw $this->createEiPropException($eiPropExtraction, new \Exception('tbd'));
				}
				
				$this->applyEiProps($forkedEiPropExtractions, $eiPropCollection, 
						$entityPropertyNames, $eiPropWrapper->getEiPropPath());
			} catch (\Throwable $t) {
				if (null === $this->eiErrorResult) {
					throw $t;
				}
				
				$eiPropPath = (null !== $contextEiPropPath) ? $contextEiPropPath->ext($eiPropExtraction->getId()) 
						: EiPropPath::create($eiPropExtraction->getId());
				
				$this->eiErrorResult->putEiPropError(
						new EiPropError($eiPropCollection->getEiMask()->getEiTypePath(), $eiPropPath, $t));
			}
		}
	}
	
	/**
	 * @param EiPropExtraction $eiPropExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws IncompatiblePropertyException
	 * @throws TypeNotFoundException
	 * @return EiProp
	 */
	public function createEiProp(EiPropExtraction $eiPropExtraction, EiMask $eiMask, array $contextEntityPropertyNames) {
		$id = $eiPropExtraction->getId();
		$eiPropClass = ReflectionUtils::createReflectionClass($eiPropExtraction->getClassName());
		
		if (!$eiPropClass->implementsInterface('rocket\ei\component\prop\indepenent\IndependentEiProp')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiPropClass->getName()
					. '\' must implement \'rocket\ei\component\prop\indepenent\IndependentEiProp\'.');
		}
		
		$eiProp = $eiPropClass->newInstance();
		
		$moduleNamespace = null;
		if ($eiMask->isExtension()) {
			$moduleNamespace = $eiMask->getExtension()->getModuleNamespace();
		} else {
			$moduleNamespace = $eiMask->getEiType()->getModuleNamespace();
		}
		$eiProp->setLabelLstr(Rocket::createLstr($eiPropExtraction->getLabel() ?? StringUtils::pretty($id), $moduleNamespace));
		
		$eiPropConfigurator = $eiProp->createEiPropConfigurator();
		ArgUtils::valTypeReturn($eiPropConfigurator, EiPropConfigurator::class, $eiProp,
				'createEiPropConfigurator');
		IllegalStateException::assertTrue($eiPropConfigurator instanceof EiPropConfigurator);
		$eiPropConfigurator->setDataSet(new DataSet($eiPropExtraction->getProps()));
		
		$objectPropertyName = $eiPropExtraction->getObjectPropertyName();
		$entityPropertyName = $eiPropExtraction->getEntityPropertyName();
		
		
		if ($this->setupQueue === null) {
			return $eiProp;
		}
		
		$this->setupQueue->addPropIn(new PropIn($eiMask->getEiType(), $eiPropConfigurator, $objectPropertyName, 
				$entityPropertyName, $contextEntityPropertyNames));
		
		$this->setupQueue->addEiPropConfigurator($eiProp, $eiPropConfigurator);
		
		return $eiProp;
	}
	
	/**
	 * @param EiComponentExtraction $configurableExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiCommand
	 */
	public function createEiCommand(EiComponentExtraction $configurableExtraction, EiMask $eiMask) {
		$eiCommandClass = ReflectionUtils::createReflectionClass($configurableExtraction->getClassName());
		
		if (!$eiCommandClass->implementsInterface('rocket\ei\component\command\IndependentEiCommand')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiCommandClass->getName()
					. '\' must implement \'rocket\ei\component\command\IndependentEiCommand\'.');
		}
		
		$eiCommand = $eiCommandClass->newInstance();
		
		$eiConfigurator = $eiCommand->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, 'rocket\ei\component\EiConfigurator',
				$eiCommand, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setDataSet(new DataSet($configurableExtraction->getProps()));
		
		if ($this->setupQueue !== null) {
			$this->setupQueue->addEiCommandConfigurator($eiCommand, $eiConfigurator);
		}
		
		return $eiCommand;
	}
	
	/**
	 * @param EiComponentExtraction $eiModificatorExtraction
	 * @param EiType $eiType
	 * @param EiMask $eiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @throws TypeNotFoundException
	 * @return IndependentEiModificator
	 */
	public function createEiModificator(EiModificatorExtraction $eiModificatorExtraction, EiMask $eiMask) {
		$eiModificatorClass = ReflectionUtils::createReflectionClass($eiModificatorExtraction->getClassName());
		
		if (!$eiModificatorClass->implementsInterface('rocket\ei\component\modificator\IndependentEiModificator')) {
			throw new InvalidEiComponentConfigurationException('\'' . $eiModificatorClass->getName()
					. '\' must implement \'rocket\ei\component\modificator\IndependentEiModificator\'.');
		}
		
		$eiModificator =  $eiModificatorClass->newInstance();
		
		$eiConfigurator = $eiModificator->createEiConfigurator();
		ArgUtils::valTypeReturn($eiConfigurator, EiConfigurator::class, $eiModificator, 'creatEiConfigurator');
		IllegalStateException::assertTrue($eiConfigurator instanceof EiConfigurator);
		$eiConfigurator->setDataSet(new DataSet($eiModificatorExtraction->getProps()));
		
		if ($this->setupQueue !== null) {
			$this->setupQueue->addEiModificatorConfigurator($eiModificator, $eiConfigurator);
		}
		
		return $eiModificator;
	}
	
	
	/**
	 * @param EiTypeExtensionExtraction $eiTypeExtensionExtraction
	 * @param EiModificatorExtraction[] $eiModificatorExtractions
	 */
	public function createEiTypeExtension(EiMask $extenedEiMask, EiTypeExtensionExtraction $eiTypeExtensionExtraction,
			array $eiModificatorExtractions) {
				
		$eiMask = new EiMask($extenedEiMask->getEiType());
		$eiTypeExtension = new EiTypeExtension($eiTypeExtensionExtraction->getId(),
				$eiTypeExtensionExtraction->getModuleNamespace(),
				$eiMask, $extenedEiMask);
		
		$this->asdf($eiTypeExtensionExtraction->getEiMaskExtraction(), $eiMask, $eiModificatorExtractions);
		
		return $eiTypeExtension;
	}
	
	private function createEiTypeException($eiTypeId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create EiType (id: ' . $eiTypeId . ').', 0, $previous);
	}
	
	private function createEiPropException(EiPropExtraction $eiPropExtraction, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create ' . $eiPropExtraction->getClassName()
				. ' [id: ' . $eiPropExtraction->getId() . '].', 0, $previous);
	}
	
	private function createEiCommandException($eiCommandId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiCommand (id: ' . $eiCommandId . ').', 0, $previous);
	}
	
	private function createEiModificatorException($eiModificatorId, \Exception $previous) {
		return new InvalidEiComponentConfigurationException('Could not create EiModificatior (id: ' . $eiModificatorId . ').', 0, $previous);
	}
	
	private function createEiMaskException($eiMaskId, \Exception $previous) {
		return new InvalidSpecConfigurationException('Could not create EiMask (id: ' . $eiMaskId . ').', 0, $previous);
	}	
}