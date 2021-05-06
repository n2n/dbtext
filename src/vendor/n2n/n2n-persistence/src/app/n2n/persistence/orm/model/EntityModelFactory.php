<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\orm\model;

use n2n\util\col\ArrayUtils;
use n2n\reflection\ReflectionContext;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\InheritanceType;
use n2n\persistence\orm\property\SetupProcess;
use n2n\persistence\orm\OrmConfigurationException;
use n2n\persistence\orm\property\BasicEntityProperty;
use n2n\web\dispatch\model\ModelInitializationException;
use n2n\persistence\orm\property\PropertyInitializationException;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\property\ClassSetup;
use n2n\persistence\orm\OrmErrorException;
use n2n\persistence\orm\property\IdDef;
use n2n\persistence\orm\LifecycleUtils;
use n2n\persistence\orm\annotation\AnnoMappedSuperclass;

class EntityModelFactory {
	const DEFAULT_ID_PROPERTY_NAME = 'id';
	const DEFAULT_DISCRIMINATOR_COLUMN = 'discr';
	
	private $entityPropertyProviderClassNames;
	private $entityPropertyProviders;
	private $defaultNamingStrategy;
	private $onFinalizeQueue;
	
	private $annotationSet;
	private $entityModel;
	private $nampingStrategy;
	private $currentsetupProcess;
	/**
	 * @param array $entityPropertyProviderClassNames
	 */
	public function __construct(array $entityPropertyProviderClassNames, 
			$defaultNamingStrategyClassName = null) {
		$this->entityPropertyProviderClassNames = $entityPropertyProviderClassNames;
		$this->onFinalizeQueue = new OnFinalizeQueue();
	
		if ($defaultNamingStrategyClassName === null) {
			$this->defaultNamingStrategy = new HyphenatedNamingStrategy();
			return;
		} 
		
		$class = ReflectionUtils::createReflectionClass($defaultNamingStrategyClassName);
		if (!$class->implementsInterface('n2n\persistence\orm\model\NamingStrategy')) {
			throw new \InvalidArgumentException('Naming strategy class must implement interface'
					. ' n2n\persistence\orm\model\NamingStrategy: ' . $defaultNamingStrategyClassName);
		}
		$this->defaultNamingStrategy = ReflectionUtils::createObject($class);
	}
	/**
	 * @return array
	 */
	public function getEntityPropertyProviderClassNames() {
		return $this->entityPropertyProviderClassNames;
	}
	/**
	 * @throws OrmConfigurationException
	 * @return \n2n\persistence\orm\property\EntityPropertyProvider[]
	 */
	private function getEntityPropertyProviders() {
		if ($this->entityPropertyProviders !== null) {
			return $this->entityPropertyProviders;
		}
		
		$this->entityPropertyProviders = array();
		foreach ($this->entityPropertyProviderClassNames as $entityPropertyProviderClassName) {
			$providerClass = ReflectionUtils::createReflectionClass($entityPropertyProviderClassName);
			if (!$providerClass->isSubclassOf('n2n\persistence\orm\property\EntityPropertyProvider')) {
				throw new OrmConfigurationException('EntityPropertyProvider must implement ' 
						. 'interface n2n\persistence\orm\property\EntityPropertyProvider: ' 
						. $providerClass->getName());
			}	

			$this->entityPropertyProviders[] = $providerClass->newInstance();
		}
		
		return $this->entityPropertyProviders;
	}
	/**
	 * @param \ReflectionClass $entityClass
	 * @param EntityModel $superEntityModel
	 * @return \n2n\persistence\orm\model\EntityModel
	 */
	public function create(\ReflectionClass $entityClass, EntityModel $superEntityModel = null) {
		if ($this->currentsetupProcess !== null) {
			throw new IllegalStateException('SetupProcess not finished.');
		}
		
		$this->annotationSet = ReflectionContext::getAnnotationSet($entityClass);
		
		if (null !== $this->annotationSet->getClassAnnotation(AnnoMappedSuperclass::class)) {
			throw new ModelInitializationException('Could not initialize MappedSuperclass as entity: '
					. $entityClass->getName());
		}
		
		$this->entityModel = $entityModel = new EntityModel($entityClass, $superEntityModel);
		
		$this->currentsetupProcess = new SetupProcess($this->entityModel, 
				new EntityPropertyAnalyzer($this->getEntityPropertyProviders()),
				$this->onFinalizeQueue);
		$this->setupProcesses[$entityClass->getName()] = $this->currentsetupProcess;
		
		if ($superEntityModel !== null) {
			$superEntityClassName = $superEntityModel->getClass()->getName();
			IllegalStateException::assertTrue(isset($this->setupProcesses[$superEntityClassName]));
			$this->currentsetupProcess->inherit($this->setupProcesses[$superEntityClassName]);
		}
		
		$this->nampingStrategy = $this->defaultNamingStrategy;
		if (null !== ($annoNamingStrategy = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoNamingStrategy'))) {
			$this->nampingStrategy = $annoNamingStrategy->getNamingStrategy();
		}
		
		$this->analyzeInheritanceType();
		$this->analyzeDiscriminatorColumn();
		$this->analyzeDiscriminatorValue();
		$this->analyzeTable();
		$this->analyzeCallbacks();
		try {
			$this->analyzeProperties();
			$this->analyzeId();
		} catch (PropertyInitializationException $e) {
			throw new ModelInitializationException('Could not initialize entity: '
					. $this->entityModel->getClass()->getName(), 0, $e);
		}
		
		return $entityModel;
	}
	
	public function cleanUp(EntityModelManager $entityModelManager) {
		if ($this->currentsetupProcess === null) {
			throw new IllegalStateException('No pending SetupProcess');
		}
				
		$this->currentsetupProcess = null;
		$this->annotationSet = null;
		$this->entityModel = null;
		$this->propertiesAnalyzer = null;
		
		$this->onFinalizeQueue->finalize($entityModelManager);
	}
	
	/**
	 * 
	 */
	private function analyzeInheritanceType() {
		$superEntityModel = $this->entityModel->getSuperEntityModel();
		
		if (null !== $superEntityModel && null == $superEntityModel->getInheritanceType()) {
			throw OrmErrorException::create('No inheritance strategy defined in supreme class of'
							.  $this->entityModel->getClass()->getName(),  
					array($this->entityModel->getSupremeEntityModel()->getClass()));
		}
		
		$annoInheritance = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoInheritance');
		
		if (null === $annoInheritance) return;
		
		if ($superEntityModel !== null) {
			throw OrmErrorException::create('Inheritance strategy of ' . $this->entityModel->getClass()->getName()
							. 'has to be specified in supreme class', array($annoInheritance));
		}

		$this->entityModel->setInheritanceType($annoInheritance->getStrategy());

		if ($annoInheritance->getStrategy() == InheritanceType::SINGLE_TABLE) {
			$annoDiscriminatorColumn = $this->annotationSet->getClassAnnotation('n2n\persistence\orm\annotation\AnnoDiscriminatorColumn');
			if ($annoDiscriminatorColumn === null) {
				$discriminatorColumnName = self::DEFAULT_DISCRIMINATOR_COLUMN;
			} else {
				$discriminatorColumnName = $annoDiscriminatorColumn->getColumnName(); 
			}
			$this->entityModel->setDiscriminatorColumnName($discriminatorColumnName);
		}
	}
	/**
	 * 
	 */
	private function analyzeDiscriminatorColumn() {
		$annoDiscriminatorValue = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoDiscriminatorValue');
		
		if ($annoDiscriminatorValue === null) {
			if ($this->entityModel->getInheritanceType() == InheritanceType::SINGLE_TABLE
					&& !$this->entityModel->getClass()->isAbstract()) {
				throw OrmErrorException::create('No discriminator value defined for entity: '
						. $this->class->getName(), array($annoDiscriminatorValue));
			}
			
			return;
		}
		
		if ($this->entityModel->getInheritanceType() != InheritanceType::SINGLE_TABLE) {
			throw OrmErrorException::create('Discriminator value can only be defined for entities with inheritance type SINGLE_TABLE'
					. $this->entityModel->getClass()->getName(), array($annoDiscriminatorValue));
		}

		if ($this->entityModel->getClass()->isAbstract()) {
			throw OrmErrorException::create('Discriminator value must not be defined for abstract entity: '
					. $this->entityModel->getClass()->getName(), array($annoDiscriminatorValue));
		}
			
		$this->entityModel->setDiscriminatorValue($annoDiscriminatorValue->getValue());
	}
	/**
	 * 
	 */
	private function analyzeDiscriminatorValue() {
		if ($this->entityModel->getInheritanceType() != InheritanceType::SINGLE_TABLE 
				|| $this->entityModel->getClass()->isAbstract()) {
			return;
		}
		
		if (null !== ($annoDiscriminatorValue = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoDiscriminatorValue'))) {
			$this->entityModel->setDiscriminatorValue($annoDiscriminatorValue->getValue());
			return;
		}
		
		throw OrmErrorException::create('No discriminator value defined for entity: '
				. $this->entityModel->getClass()->getName(), array($this->entityModel->getClass()));
	}
	/**
	 * 
	 */
	private function analyzeTable() {
		if ($this->entityModel->getInheritanceType() == InheritanceType::SINGLE_TABLE 
				&& $this->entityModel->hasSuperEntityModel()) {
			$this->entityModel->setTableName($this->entityModel->getSuperEntityModel()->getTableName());
			return;
		} 
		
		$tableName = null;
		if (null !== ($annoTable = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoTable'))) {
			$tableName = $annoTable->getName();
		} 
		
		$this->entityModel->setTableName($this->nampingStrategy->buildTableName(
				$this->entityModel->getClass(), $tableName));
	}
	/**
	 * 
	 */
	private function analyzeCallbacks() {
		$class = $this->entityModel->getClass();
		foreach ($class->getMethods() as $method) {
			if ($method->getDeclaringClass() != $class) continue;

			$eventType = LifecycleUtils::identifyEvent($method->getName());
			if ($eventType === null) continue;
			
			$this->entityModel->addLifecycleMethod($eventType, $method);
		}
		
		$annoEntityListener = $this->annotationSet->getClassAnnotation(
				'n2n\persistence\orm\annotation\AnnoEntityListeners');
		if ($annoEntityListener !== null) {
			$this->entityModel->setEntityListenerClasses($annoEntityListener->getClasses());
		}
	}
	/**
	 * 
	 */
	private function analyzeProperties() {
		$classSetup = new ClassSetup($this->currentsetupProcess, $this->entityModel->getClass(), 
				$this->nampingStrategy);
		$this->currentsetupProcess->getEntityPropertyAnalyzer()->analyzeClass($classSetup);
			
		foreach ($classSetup->getEntityProperties() as $property) {
			$this->entityModel->addEntityProperty($property);
		}
	}
	/**
	 * 
	 */
	private function analyzeId() {
		$annoIds = $this->annotationSet->getPropertyAnnotationsByName('n2n\persistence\orm\annotation\AnnoId');
		if (count($annoIds) > 1) {
			throw OrmErrorException::create('Multiple ids defined in Entity: ' 
					. $this->entityModel->getClass()->getName(), $annoIds);
		} 
		
		$propertyName = self::DEFAULT_ID_PROPERTY_NAME;
		$generatedValue = $this->entityModel->getInheritanceType() != InheritanceType::TABLE_PER_CLASS;
		$sequenceName = null;
		
		$annoId = ArrayUtils::current($annoIds);
		if ($annoId === null) {
			if ($this->entityModel->hasSuperEntityModel()) return;
		} else {
			if ($this->entityModel->hasSuperEntityModel()) {
				throw OrmErrorException::create(
						'Id for ' . $this->class->getName() . ' already defined in super class '
								. $this->entityModel->getSuperEntityModel()->getClass()->getName(),
						array($annoId));
			}
			
			$propertyName = $annoId->getAnnotatedProperty()->getName();
			$generatedValue = $annoId->isGenerated();
			if ($generatedValue && $this->entityModel->getInheritanceType() == InheritanceType::TABLE_PER_CLASS) {
				throw OrmErrorException::create(
						'Ids with generated values are not compatible with inheritance type TABLE_PER_CLASS in ' 
								. $this->entityModel->getClass()->getName() . '.', $annoIds);
			}
			$sequenceName = $annoId->getSequenceName();
		}

		try {
			$idProperty = $this->entityModel->getEntityPropertyByName($propertyName);
			if ($idProperty instanceof BasicEntityProperty) {
				$this->entityModel->setIdDef(new IdDef($idProperty, $generatedValue, $sequenceName));
				return;
			}
			throw $this->currentsetupProcess->createPropertyException('Invalid property type for id.', null, $annoIds);
		} catch (UnknownEntityPropertyException $e) {
			throw $this->currentsetupProcess->createPropertyException('No id property defined.', $e, $annoIds);
		}
	}
}

class OnFinalizeQueue {
	private $onFinalizeClosures = array();
	private $entityModelManager = null;
	
	public function onFinalize(\Closure $closure, $prepend = false) {
		if ($prepend) {
			array_unshift($this->onFinalizeClosures, $closure);
		} else {
			$this->onFinalizeClosures[] = $closure;
		}
	}
	
	public function finalize(EntityModelManager $entityModelManager) {
		if ($this->entityModelManager !== null) return;
		$this->entityModelManager = $entityModelManager;
		while (null !== ($onFinalizeClosure = array_shift($this->onFinalizeClosures))) {
			$onFinalizeClosure($entityModelManager);
		}
		$this->entityModelManager = null;
	}
} 