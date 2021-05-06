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
namespace rocket\core\model;

use n2n\persistence\orm\EntityManager;
use rocket\spec\Spec;
use n2n\context\RequestScoped;
use rocket\spec\EiComponentStore;
use n2n\core\container\PdoPool;
use n2n\core\container\N2nContext;
use rocket\spec\source\N2nContextRocketConfigSource;
use rocket\ei\manage\draft\DraftManager;
use rocket\spec\extr\SpecExtractionManager;
use rocket\core\model\launch\LayoutExtractionManager;
use rocket\core\model\launch\Layout;
use n2n\l10n\Lstr;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ControllerContext;

class Rocket implements RequestScoped {
	const VERSION = '3.0.1';
	const NS = 'rocket';
	
	private $dbhPool;
	private $n2nContext;
	
	private $rocketConfigSource;
	private $spec;
	private $layoutManager;
	private $eiComponentStore;
	private $controllerContext;
	
	private function _init(PdoPool $dbhPool, N2nContext $n2nContext) {
		$this->dbhPool = $dbhPool;
		$this->n2nContext = $n2nContext;
	}
	
// 	public function __construct() {
// 		$this->entityModelManager = EntityModelManager::getInstance();
// 	}

	public function getRocketConfigSource() {
		if ($this->rocketConfigSource === null) {  
			$this->rocketConfigSource = new N2nContextRocketConfigSource($this->n2nContext);
		}
		
		return $this->rocketConfigSource;
	}
	
	public function setControllerContext(?ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}
	
	public function isActive() {
		return $this->controllerContext !== null;
	}
	
	/**
	 * @return Layout
	 */
	public function getLayout() {
		if ($this->layoutManager === null) {
			$rocketConfigSource = $this->getRocketConfigSource();
			$lcsd = new LayoutExtractionManager($rocketConfigSource->getLayoutConfigSource());
			$lcsd->load();
			$this->layoutManager = new Layout($lcsd, $this->getSpec());
		}
		
		return $this->layoutManager;
	}
	
	/**
	 * @return \rocket\spec\Spec
	 */
	public function getSpec(): Spec {
		if ($this->spec !== null) {
			return $this->spec;
		}
		
		$rocketConfigSource = $this->getRocketConfigSource();
		
		$sem = new SpecExtractionManager($rocketConfigSource->getSpecsConfigSource(), 
				$rocketConfigSource->getModuleNamespaces());

		$this->spec = new Spec($sem, $this->dbhPool->getEntityModelManager(), $this->n2nContext, 0);
// 		$this->spec->eagerInit();
		return $this->spec;
	}
	
	/**
	 * @return \rocket\spec\EiComponentStore
	 */
	public function getEiComponentStore() {
		if ($this->eiComponentStore === null) {
			$rocketConfigSource = $this->getRocketConfigSource();
			$this->eiComponentStore = new EiComponentStore($rocketConfigSource->getElementsConfigSource(), 
					$rocketConfigSource->getModuleNamespaces());
		}
		
		return $this->eiComponentStore;
	}
// 	/**
// 	 * @param EntityManager $em
// 	 * @return TranslationManager
// 	 */
// 	public function getOrCreateTranslationManager(EntityManager $em) {
// 		$emObjHash = spl_object_hash($em);
// 		if (!isset($this->translationManagers[$emObjHash])) {
// 			$this->translationManagers[$emObjHash] = new ScriptTranslationManager($this->getSpec(), $em);
// 		}
		
// 		return $this->translationManagers[$emObjHash];
// 	}
	
	public function getOrCreateDraftManager(EntityManager $em) {
		$emObjHash = spl_object_hash($em);
		if (!isset($this->draftManagers[$emObjHash])) {
			$this->draftManagers[$emObjHash] = new DraftManager($this->getSpec(), $em, $this->n2nContext);
		}
		
		return $this->draftManagers[$emObjHash];
	}
	
// 	private function getTranslationModel($entity, EntityManager $em = null) {
// 		$className = get_class($entity);
// 		if (isset($this->translationModels[$className])) {
// 			return $this->translationModels[$className];
// 		}
		
// 		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
// 		$eiType = $this->getSpec()->getEiTypeByClass($entityModel->getClass());
// 		if ($em === null) {
// 			$em = $eiType->lookupEntityManager(N2N::getPdoPool());
// 		}
		
// 		$translationModel = TranslationModelFactory::createTranslationModel($em, $eiType);
// 		if (!isset($this->translationModels[$className])) {
// 			$this->translationModels[$className] = $translationModel;
// 		}
		
// 		return $this->translationModels[$className];
// 	}
	
// 	public function translate($entity, N2nLocale $n2nLocale, EntityManager $em = null) {
// 		if (N2nLocale::getDefault()->equals($n2nLocale)) {
// 			return $entity;
// 		}

// 		$entityModel = $this->entityModelManager->getEntityModelByObject($entity);
// 		$eiType = $this->getSpec()->getEiTypeByClass($entityModel->getClass());
// 		if ($em === null) {
// 			$em = $eiType->lookupEntityManager($this->dbhPool);
// 		}
		
// 		$translationManager = $this->getOrCreateTranslationManager($em);
			
// 		return $translationManager->find($entity, $n2nLocale, true)
// 				->getTranslatedEntity();
// 	}
	
// 	public function translateArray($entries, N2nLocale $n2nLocale, EntityManager $em = null) {		
// 		$translatedEntries = array();
// 		if ($entries instanceof \ArrayObject) {
// 			$translatedEntries = new \ArrayObject();
// 		}
		
// 		foreach ($entries as $key => $entry) {
// 			$translatedEntries[$key] = $this->translate($entry, $n2nLocale, $em);
// 		}
// 		return $translatedEntries;
// 	}
	
// 	public function listen(EntityManager $em) {
// 		if ($this->rocketEntityStateListener === null) {
// 			$this->rocketEntityStateListener = new RocketEntityStateListener($this);
// 		}
		
// 		$em->getPersistenceContext()->registerEntityStateListener($this->rocketEntityStateListener);
// 	}

	private static $cachedDtcs = [];
	
	/**
	 * @param string $code
	 * @param string $moduleNamespace
	 * @return \n2n\l10n\Lstr
	 */
	static function createLstr(string $code, string $moduleNamespace) {
// 		return Lstr::create($code);

// 		return Lstr::createCode($code, $moduleNamespace)->addLangNs($moduleNamespace
// 				. '\\' . DynamicTextCollection::LANG_NS_EXT . '\\' . Rocket::NS);
		
		$dtc = null;
		if (isset(self::$cachedDtcs[$moduleNamespace])) {
			$dtc = self::$cachedDtcs[$moduleNamespace];
		} else {
			$dtc = self::$cachedDtcs[$moduleNamespace] = (new DynamicTextCollection($moduleNamespace, null))
					->addLangNamespace($moduleNamespace . '\\' . DynamicTextCollection::LANG_NS_EXT . '\\' . Rocket::NS);
		}

		return Lstr::createCodeDtc($code, $dtc);
	}
	
	/**
	 * @param string $code
	 * @param string $moduleNamespace
	 * @return NULL|\n2n\l10n\Lstr
	 */
	static function buildLstr(?string $code, string $moduleNamespace) {
		if ($code === null) return null; 
		
		return self::createLstr($code, $moduleNamespace);
	}
}
