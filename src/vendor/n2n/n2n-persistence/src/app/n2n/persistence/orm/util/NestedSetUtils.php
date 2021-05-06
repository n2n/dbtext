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
namespace n2n\persistence\orm\util;

use n2n\persistence\meta\data\OrderDirection;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaFunction;
use n2n\core\N2N;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\JoinType;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\OrmUtils;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\OrmException;

class NestedSetUtils {
	const DEFAULT_LEFT_PROPERTY_NAME = 'lft';
	const DEFAULT_RIGHT_PROPERTY_NAME = 'rgt';
	const NODE_ALIAS = 'node';
	const PARENT_ALIAS = 'parent';
	const LEVEL_ALIAS = 'level';
	
	const RESULT_LEVEL_ALIAS = 'l';
	const RESULT_ENTITY_ALIAS = 'e';
	
	private $em;
	private $class;
	private $entityModel;
	private $leftCriteriaProperty;
	private $rightCriteriaProperty;
	private $leftEntityProperty;
	private $rightEntityProperty;
	private $leftEcp;
	private $rightEcp;
	
	public function __construct(EntityManager $em, \ReflectionClass $class, NestedSetStrategy $nestedSetStrategy = null) {
		$this->em = $em;
		$this->class = $class;
		$this->entityModel = $em->getEntityModelManager()->getEntityModelByClass($this->class);
		$this->setStrategy($nestedSetStrategy);
	}
	
	/**
	 * @param NestedSetStrategy $nestedSetStrategy
	 */
	public function setStrategy(NestedSetStrategy $nestedSetStrategy = null) {
		if ($nestedSetStrategy === null) {
			$this->leftCriteriaProperty = CrIt::p(self::DEFAULT_LEFT_PROPERTY_NAME);
			$this->rightCriteriaProperty = CrIt::p(self::DEFAULT_RIGHT_PROPERTY_NAME);
		} else {
			$this->leftCriteriaProperty = $nestedSetStrategy->getLeftCriteriaProperty();
			$this->rightCriteriaProperty = $nestedSetStrategy->getRightCriteriaProperty();
		}
		
		$this->leftEntityProperty = OrmUtils::determineEntityProperty($this->entityModel, $this->leftCriteriaProperty);
		$this->rightEntityProperty = OrmUtils::determineEntityProperty($this->entityModel, $this->rightCriteriaProperty);
		$this->leftEcp = CrIt::p('e', $this->leftCriteriaProperty);
		$this->rightEcp = CrIt::p('e', $this->rightCriteriaProperty);
	}
	
	/**
	 * @return \n2n\persistence\orm\util\NestedSetStrategy
	 */
	public function getStrategy(): NestedSetStrategy {
		return new NestedSetStrategy($this->leftCriteriaProperty, $this->rightCriteriaProperty);
	}
	
	/**
	 * @param object $entityObj
	 * @throws UnknownEntryException
	 * @return array
	 */
	private function lookupLftRgt($entityObj): array {
		$criteria = $this->em->createCriteria()
				->select($this->leftEcp, 'lft')
				->select($this->rightEcp, 'rgt')
				->from($this->class, 'e')
				->where(array('e' => $entityObj))->endClause();
		if (null !== ($result = $criteria->toQuery()->fetchSingle())) {
			return $result;	
		}
		
		throw new UnknownEntryException();
	}
	
	/**
	 * @param object $entity
	 * @param int $lft
	 * @throws IllegalStateException
	 */
	private function updateLft($entity, $lft) {
		if (!$this->leftCriteriaProperty->hasMultipleLevels()) {
			$this->leftEntityProperty->writeValue($entity, $lft);
		} else {
			$leftObject = OrmUtils::determineValue($this->entityModel, $entity, $this->leftCriteriaProperty);
			if (!is_object($leftObject)) {
				throw new IllegalStateException('Exception message not yet implemented.');
			}
			$this->leftCriteriaProperty->writevalue($leftObject, $lft);
		}
	}
	
	/**
	 * @param object $entity
	 * @param int $rgt
	 * @throws IllegalStateException
	 */
	private function updateRgt($entity, $rgt) {
		if (!$this->rightCriteriaProperty->hasMultipleLevels()) {
			$this->rightEntityProperty->writeValue($entity, $rgt);
		} else {
			$rightObject = OrmUtils::determineValue($this->entityModel, $entity, $this->rightCriteriaProperty);
			if (!is_object($rightObject)) {
				throw new IllegalStateException('Exception message not yet implemented.');
			}
			$this->rightCriteriaProperty->writevalue($rightObject, $rgt);
		}
	}
	
	/**
	 * @return \n2n\persistence\orm\util\NestedSetItem[] 
	 */
	public function fetch($baseEntity = null, $descendantsOnly = false, Criteria $criteria = null): array {
		if ($criteria === null) {
			$criteria = $this->em->createCriteria()->from($this->class, self::NODE_ALIAS);
		}
		
		$criteria->select(CrIt::f(CriteriaFunction::COUNT, CrIt::c(1)), self::RESULT_LEVEL_ALIAS)
				->select(self::NODE_ALIAS, self::RESULT_ENTITY_ALIAS);
		$criteria->join($this->class, self::PARENT_ALIAS, JoinType::CROSS);
		$criteria->where()
				->andMatch(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), 
						CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO,
						CrIt::p(self::PARENT_ALIAS, $this->leftCriteriaProperty))
				->andMatch(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), 
						CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO,
						CrIt::p(self::PARENT_ALIAS, $this->rightCriteriaProperty));
		
		if ($baseEntity !== null) {
			$result = $this->lookupLftRgt($baseEntity);
			if ($result === null) return array();
			$baseLft = $result['lft'];
			$baseRgt = $result['rgt'];
			
			$criteria->where()
					->match(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty),
							($descendantsOnly ? CriteriaComparator::OPERATOR_LARGER_THAN 
									: CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO), 
							CrIt::c($baseLft))
					->andMatch(CrIt::p(self::NODE_ALIAS, $this->rightCriteriaProperty),
							($descendantsOnly ? CriteriaComparator::OPERATOR_SMALLER_THAN 
									: CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO),
							CrIt::c($baseRgt))
					->andMatch(CrIt::p(self::PARENT_ALIAS, $this->leftCriteriaProperty),
							($descendantsOnly ? CriteriaComparator::OPERATOR_LARGER_THAN 
									: CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO),
							CrIt::c($baseLft))
					->andMatch(CrIt::p(self::PARENT_ALIAS, $this->rightCriteriaProperty),
							($descendantsOnly ? CriteriaComparator::OPERATOR_SMALLER_THAN
									: CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO),
							CrIt::c($baseRgt));
		}
		
		$criteria->group(CrIt::p(self::NODE_ALIAS, $this->entityModel->getIdDef()->getEntityProperty()));
		$criteria->order(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), OrderDirection::ASC);

		$items = array();
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$items[] = new NestedSetItem($result[self::RESULT_LEVEL_ALIAS] - 1, $result[self::RESULT_ENTITY_ALIAS]);
		}
		
		return $items;
	}
	
	public function fetchParents($entityObj, bool $includeSelf = false, string $direction = 'DESC', Criteria $criteria = null) {
		if ($criteria === null) {
			$criteria = $this->em->createCriteria()->from($this->class, self::NODE_ALIAS);
		}
		
		$result = $this->lookupLftRgt($entityObj);
		if ($result === null) return array();
		
		$lft = $result['lft'];
		$rgt = $result['rgt'];
		
		$criteria->select(self::NODE_ALIAS, self::RESULT_ENTITY_ALIAS)
				->where()
				->match(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), ($includeSelf ? '<=' : '<'), $lft)
				->andMatch(CrIt::p(self::NODE_ALIAS, $this->rightCriteriaProperty), ($includeSelf ? '>=' : '>'), $rgt);
		$criteria->order(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), $direction);
		
		$result =  $criteria->toQuery()->fetchArray();
		foreach ($result as $key => $resultEntry) {
			if (count($resultEntry) > 1) return $result;
			
			$result[$key] = $resultEntry[self::RESULT_ENTITY_ALIAS];
		}
		
		return $result;
	}
	
	public function fetchLevel($entityObj, Criteria $criteria = null) {
		if ($criteria === null) {
			$criteria = $this->em->createCriteria()->from($this->class, self::NODE_ALIAS);
		}
		
		$result = $this->lookupLftRgt($entityObj);
		if ($result === null) return array();
		
		$lft = $result['lft'];
		$rgt = $result['rgt'];
		
		$criteria->select(CrIt::f('COUNT', CrIt::c('1')))
				->where()
				->match(CrIt::p(self::NODE_ALIAS, $this->leftCriteriaProperty), '<', $lft)
				->andMatch(CrIt::p(self::NODE_ALIAS, $this->rightCriteriaProperty), '>', $rgt);
		
		return $criteria->toQuery()->fetchSingle();		
	}
	
	/**
	 * @param object $entity
	 */
	public function remove($entity) {
		$this->em->flush();
		
		$result = $this->lookupLftRgt($entity);
		if ($result === null) return array();
		
		$lft = $result['lft'];
		$rgt = $result['rgt'];
		$diff = $rgt - $lft + 1;
		
		$nestedSetItems = $this->fetch($entity, false);
		foreach ($nestedSetItems as $nestedSetItem) {
			$this->em->remove($nestedSetItem->getEntityObj());
		}
		$this->em->flush();
		
		$criteria = $this->em->createCriteria($this->class, 'e');
		$criteria->select('e', 'entity')->select($this->leftEcp, 'lft')
				->from($this->class, 'e')
				->where()->match($this->leftEcp, 
						CriteriaComparator::OPERATOR_LARGER_THAN, CrIt::c($lft));
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] - $diff);
// 			$this->em->merge($fetchedObject);
		}
		$this->em->flush();
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->rightEcp, 'rgt')
				->from($this->class, 'e')
				->where()->match($this->rightEcp,
						CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, CrIt::c($rgt));
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateRgt($result['entity'], $result['rgt'] - $diff);
// 			$this->em->merge($fetchedObject);
		}
		$this->em->flush();
	}
	
	/**
	 * @return NULL|mixed
	 */
	private function lookupMaxRgt() {
		$rcp = $this->rightEcp;
		$criteria = $this->em->createCriteria();
		$criteria->select($rcp)
				->from($this->class, 'e')
				->order($rcp, Criteria::ORDER_DIRECTION_DESC)
				->limit(0, 1);
		
		return $criteria->toQuery()->fetchSingle();
	}
	
	/**
	 * @param object $object
	 */
	public function insertRoot($object) {
		OrmUtils::initializeProxy($this->em, $object);
		$this->em->flush();
		
		$lft = 1;
		$rgt = 2;
		if (null !== ($maxRgt = $this->lookupMaxRgt($object))) {
			$lft = $maxRgt + 1;
			$rgt = $lft + 1;
		}
	
		$this->updateLft($object, $lft);
		$this->updateRgt($object, $rgt);
			
		$this->em->persist($object);
		$this->em->flush();
	}
	
	public function valMove($entityObj, $targetEntityObj) {
		$lrResult = $this->lookupLftRgt($entityObj);
		$targetLrResult = $this->lookupLftRgt($targetEntityObj);
		
		if ($lrResult['lft'] <= $targetLrResult['lft'] && $lrResult['rgt'] >= $targetLrResult['rgt']) {
			throw new IllegalStateException('Invalid move.');
		}
	}
	
	public function moveBefore($entityObj, $beforeEntityObj) {
		$this->valMove($entityObj, $beforeEntityObj);
		$this->moveToLft($entityObj, $this->lookupLftRgt($beforeEntityObj)['lft']);
	}
	
	public function moveAfter($entityObj, $afterEntityObj) {
		$this->valMove($entityObj, $afterEntityObj);
		$this->moveToLft($entityObj, $this->lookupLftRgt($afterEntityObj)['rgt'] + 1);
	}
	
	public function insertBefore($entityObj, $beforeEntityObj) {
		$this->insertRoot($entityObj);
		$this->moveBefore($entityObj, $beforeEntityObj);
// 		$this->moveToLft($entityObj, $this->lookupLftRgt($beforeEntityObj)['lft']);
	}
	
	public function insertAfter($entityObj, $afterEntityObj) {
		$this->insertRoot($entityObj);
		$this->moveAfter($entityObj, $afterEntityObj);
// 		$this->moveToLft($entityObj, $this->lookupLftRgt($afterEntityObj)['rgt'] + 1);		
	}
	
	/**
	 * @param object $object
	 * @param object $parentObject
	 * @throws IllegalStateException
	 */
	public function insert($object, $parentObject = null) {
		if ($parentObject === null) {
			$this->insertRoot($object);
			return;
		}
		
		OrmUtils::initializeProxy($this->em, $object);
		OrmUtils::initializeProxy($this->em, $parentObject);
		$this->em->flush();
		
		$parentResult = $this->lookupLftRgt($parentObject);
// 		if ($parentResult === null) {
// 			throw new IllegalStateException('Parent does not exist in database.');
// 		}
		
		$parentLft = $parentResult['lft'];
		$parentRgt = $parentResult['rgt'];
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->leftEcp, 'lft')
				->from($this->class, 'e')
				->where()
						->match($this->leftEcp,
								CriteriaComparator::OPERATOR_LARGER_THAN, CrIt::c($parentRgt))
						->andMatch($this->rightEcp,
								CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, CrIt::c($parentRgt));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] + 2);
// 			$this->em->merge($fetchedObject);
		}
		$this->em->flush();
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select(CrIt::p($this->rightEcp), 'rgt')
				->from($this->class, 'e')
				->where()
						->andMatch($this->rightEcp,
								CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, CrIt::c($parentRgt));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateRgt($result['entity'], $result['rgt'] + 2);
// 			$this->em->merge($fetchedObject);
		}
		$this->em->flush();
		
		$this->updateLft($object, $parentRgt);
		$this->updateRgt($object, $parentRgt + 1);
		
		$this->em->persist($object);
		$this->em->flush();
	}
	
	/**
	 * @link https://rogerkeays.com/how-to-move-a-node-in-nested-sets-with-sql
	 * 
	 * @param object $object
	 * @param object $parentObject
	 * @throws IllegalStateException
	 */
	public function move($object, $parentObject = null) {
		$this->em->flush();
		
		if ($parentObject !== null) {
			$this->valMove($object, $parentObject);
		}
		
		$newLft = 1;
		if ($parentObject === null) {
			if (null !== ($maxRgt = $this->lookupMaxRgt($object))) {
				$newLft = $maxRgt + 1;
			}
		} else {
			$newLft = $this->lookupLftRgt($parentObject)['rgt'];
		}
		
		$this->moveToLft($object, $newLft);
	}
		
	/**
	 * @param object $entity
	 * @param int $newLft
	 */
	private function moveToLft($entity, $newLft) {
		OrmUtils::initializeProxy($this->em, $entity);
		
		$result = $this->lookupLftRgt($entity);
		$lft = $result['lft'];
		$rgt = $result['rgt'];
	
		// calculate position adjustment variables
		$width = $rgt - $lft + 1;
	    $distance = $newLft - $lft;
	    $tmppos = $lft;
	            
	    // backwards movement must account for new space
	    if ($distance < 0) {
	        $distance -= $width;
	        $tmppos += $width;
	    }
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->leftEcp, 'lft')
				->from($this->class, 'e')
				->where()
						->match($this->leftEcp, '>=', CrIt::c($newLft));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] + $width);
		}
		$this->em->flush();
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->rightEcp, 'rgt')
				->from($this->class, 'e')
				->where()->match($this->rightEcp, '>=', CrIt::c($newLft));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateRgt($result['entity'], $result['rgt'] + $width);
		}
		$this->em->flush();		
		

		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->leftEcp, 'lft')
				->select($this->rightEcp, 'rgt')
				->from($this->class, 'e')
				->where()
						->match($this->leftEcp, '>=', CrIt::c($tmppos))
						->andMatch($this->rightEcp, '<', CrIt::c($tmppos + $width));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] + $distance);
			$this->updateRgt($result['entity'], $result['rgt'] + $distance);
		}
		$this->em->flush();

		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->leftEcp, 'lft')
				->from($this->class, 'e')
				->where()
				->match($this->leftEcp, '>', CrIt::c($rgt));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] - $width);
		}
		$this->em->flush();
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($this->rightEcp, 'rgt')
				->from($this->class, 'e')
				->where()->match($this->rightEcp, '>', CrIt::c($rgt));
		
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateRgt($result['entity'], $result['rgt'] - $width);
		}
		$this->em->flush();
	}
	
	/**
	 * @param object $object
	 * @param bool $moveUp
	 */
	public function order($object, bool $moveUp) {
		OrmUtils::initializeProxy($this->em, $object);
		
		$this->em->flush();
		
		$leftAccessProxy = $this->entityModel->getPropertyByName($this->leftCriteriaProperty)->getAccessProxy();
		$rightAccessProxy = $this->entityModel->getPropertyByName($this->rightCriteriaProperty)->getAccessProxy();
		$rootIdAccessProxy = $this->entityModel->getPropertyByName($this->rootIdPropertyName)->getAccessProxy();
		
		$result = $this->lookupLftRgt($object);
		$lft = $result['lft'];
		$rgt = $result['rgt'];
				
		$targetResult = null;
		$fac = 1;
		
		$lcp = $this->rightEcp;
		$rcp = $this->rightEcp;
		$criteria = $this->em->createCriteria();
		if ($moveUp) {
			$criteria->select('e', 'entity')->select($lcp, 'lft')->select($rcp, 'rgt')
					->from($this->class, 'e')
					->where()->matches($rcp, '=', $lft - 1);
			$fac = -1;
			$targetResult = $criteria->toQuery()->fetchSingle();
		} else {
			$criteria->select('e', 'entity')->select($lcp, 'lft')->select($rcp, 'rgt')
					->from($this->class, 'e')
					->where()->matches($lcp, '=', $rgt + 1);
			
			$targetResult = $criteria->toQuery()->fetchSingle();
		}
		
		if (null === $targetResult) {
			return;
		}
		
		$entity2 = $targetResult['entity'];
		$lft2 = $targetResult['lft'];
		$rgt2 = $targetResult['rgt'];
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($lcp, 'lft')->select($rcp, 'rgt')
				->from($this->class, 'e')
				->where()
						->match($lcp, CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, CrIt::c($lft))
						->andMatch($rcp, CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO, CrIt::c($rgt));
		
		$mv = $fac * ($rgt2 - $lft2 + 1);
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] + $mv);
			$this->updateRgt($result['entity'], $result['rgt'] + $mv);
		}
		$this->em->flush();
		
		
		$criteria = $this->em->createCriteria();
		$criteria->select('e', 'entity')->select($lcp, 'lft')->select($rcp, 'rgt')
				->from($this->class, 'e')
				->where()
						->match($lcp, CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO, CrIt::c($lft2))
						->andMatch($lcp, CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO, CrIt::c($rgt2));

		$mv = $fac * -($rgt - $lft + 1);
		foreach ($criteria->toQuery()->fetchArray() as $result) {
			$this->updateLft($result['entity'], $result['lft'] + $mv);
			$this->updateRgt($result['entity'], $result['rgt'] + $mv);
		}
		$this->em->flush();
	}
	
	
	

// 	private function move($id, $up) {
// 		$scriptAnalyzer = new AdminTableTreeScriptAnalyzer($this->adminScript);
// 		$rootFieldName = $scriptAnalyzer->getRootField()->getName();
// 		$lftFieldName = $scriptAnalyzer->getLftField()->getName();
// 		$rgtFieldName = $scriptAnalyzer->getRgtField()->getName();
	
// 		$metaTable = $this->adminScript->getMetaTable();
// 		$dbh = $this->adminScript->getMetaTable()->getPdo();
// 		$dbh->beginTransactionIna();
	
// 		$tableEntry = $metaTable->findTableEntry($id);
// 		if (!$tableEntry) {
// 			$this->response->redirectToController();
// 		}
	
// 		$id = $tableEntry->getId();
// 		$lft = $tableEntry->__get($lftFieldName);
// 		$rgt = $tableEntry->__get($rgtFieldName);
// 		$root = $tableEntry->__get($rootFieldName);
	
// 		$tableEntry2 = null;
// 		$fac = 1;
// 		if ($up == "up") {
// 			$tableEntry2 = current($metaTable->findTableEntriesByFilter(array(
// 					$rootFieldName => $tableEntry->__get($rootFieldName),
// 					$rgtFieldName => $tableEntry->__get($lftFieldName) - 1)));
// 			$fac = -1;
// 		} else {
// 			$tableEntry2 = current($metaTable->findTableEntriesByFilter(array(
// 					$rootFieldName => $tableEntry->__get($rootFieldName),
// 					$lftFieldName => $tableEntry->__get($rgtFieldName) + 1)));
// 		}
	
// 		if (!$tableEntry2) {
// 			$this->response->redirectToController();
// 		}
	
// 		$lft2 = $tableEntry2->__get($lftFieldName);
// 		$rgt2 = $tableEntry2->__get($rgtFieldName);
	
// 		$stmt = $dbh->prepare("
// 				UPDATE {$dbh->quoteField($metaTable->getName())}
// 		SET lft = lft + ?, rgt = rgt + ?, root_id = ?
// 		WHERE root_id = ?
// 		AND lft BETWEEN ? and ?");
	
// 		$mv = $fac * ($rgt2 - $lft2 + 1);
// 		$stmt->execute(array($mv, $mv, $id,
// 				$root, $lft, $rgt));
	
// 				$mv = $fac * -($rgt - $lft + 1);
// 		 	$stmt->execute(array($mv, $mv, $root,
// 		 			$root, $lft2, $rgt2));
	
// 		$stmt = $dbh->prepare("
// 				UPDATE {$dbh->quoteField($metaTable->getName())}
// 				SET root_id = ?
// 				WHERE root_id = ?");
	
// 		 	$stmt->execute(array($root, $id));
		 	 
// 		 	$dbh->commitIna();
	
// 		 	$this->response->redirectToController();
// 	}
}


// if ($this->isNew()) {
// 	$notifier->triggerOnInsert($tableEntry);
		
// 	if (isset($this->parentId)) {
// 		$parentEntry = $metaTable->findTableEntry($this->parentId);
// 		if (is_null($parentEntry)) {
// 			throw new AdminTableTreeException("Tree parent does not exist anymore.");
// 		}

// 		$parentRoot = $parentEntry->__get($rootFieldName);
// 		$parentLft = $parentEntry->__get($lftFieldName);
// 		$parentRgt = $parentEntry->__get($rgtFieldName);

// 		$stmt = $dbh->prepare("
// 				UPDATE " . $dbh->quoteField($tableName) . "
// 				SET lft =  $sqlEscLftFieldName + 2
// 				WHERE $sqlEscRootFieldName = ?
// 				AND $sqlEscLftFieldName > ?
// 				AND $sqlEscRgtFieldName >= ?");
// 		$stmt->execute(array($parentRoot, $parentRgt, $parentRgt));

// 		$stmt = $dbh->prepare("
// 				UPDATE  " . $dbh->quoteField($tableName) . "
// 				SET rgt = {$sqlEscRgtFieldName} + 2
// 				WHERE {$sqlEscRootFieldName} = ?
// 		AND {$sqlEscRgtFieldName} >= ?");
// 		$stmt->execute(array($parentRoot, $parentRgt));

// 		$tableEntry->__set($rootFieldName, $parentRoot);
// 		$tableEntry->__set($lftFieldName, $parentRgt);
// 		$tableEntry->__set($rgtFieldName, $parentRgt + 1);

// 		$metaTable->insertTableEntry($tableEntry);
// 	} else {
// 	$tableEntry->__set($lftFieldName, 1);
// 	$tableEntry->__set($rgtFieldName, 2);
// 	$metaTable->insertTableEntry($tableEntry);

// 	$tableEntry->__set($rootFieldName, $tableEntry->getId());
// 	$metaTable->updateTableEntry($tableEntry);
// 		}
			
// 		$mc->addInfo($text->get("info_entry_added"));
// } else {
// parent::save();
	
// if (array_key_exists($this->parentId, $this->parentTreeOptions)
// && (isset($this->parentId) != isset($this->currentParentEntry)
// 		|| (isset($this->currentParentEntry) && $this->parentId != $this->currentParentEntry->getId()))) {
			
// 		$nestedSetHelper = new NN6NestedSetHelper($metaTable->getPdo(),
// 				$rootFieldName, $lftFieldName, $rgtFieldName);
// 				$nestedSetHelper->moveTableEntry($metaTable, $this->getDataCollection(), $this->parentId);
// }
	
// $mc->addInfo($text->get("info_entry_edited"));
// }


// class NN6PdoNestedSetStatementBuilder {
// 	const DEFAULT_ROOT_ID_FIELD = 'root_id';
// 	const DEFAULT_LFT_FIELD = 'lft';
// 	const DEFAULT_RGT_FIELD = 'rgt';
// 	const DEFAULT_LEVEL_FIELD = 'level';

// 	private $dbh;
// 	private $tableName;
// 	private $idFieldName;
// 	private $rootFkField;
// 	private $lftField;
// 	private $rgtField;
// 	private $levelField;
// 	/**
// 	 *
// 	 * @param NN6Pdo $dbh
// 	 * @param unknown_type $tableName
// 	 * @param unknown_type $idFieldName
// 	 * @param unknown_type $rootFkField
// 	 * @param unknown_type $lftField
// 	 * @param unknown_type $rgtField
// 	 * @param unknown_type $levelField
// 	 */
// 	public function __construct(NN6Pdo $dbh, $tableName, $idFieldName, $rootFkField = NN6PdoNestedSetStatementBuilder::DEFAULT_ROOT_ID_FIELD,
// 			$lftField = NN6PdoNestedSetStatementBuilder::DEFAULT_LFT_FIELD, $rgtField = NN6PdoNestedSetStatementBuilder::DEFAULT_RGT_FIELD,
// 			$levelField = NN6PdoNestedSetStatementBuilder::DEFAULT_LEVEL_FIELD) {
			
// 		$this->dbh = $dbh;
// 		$this->tableName = (string) $tableName;
// 		$this->idFieldName = (string) $idFieldName;
// 		$this->rootFkField = (string) $rootFkField;
// 		$this->lftField = (string) $lftField;
// 		$this->rgtField = (string) $rgtField;
// 		$this->levelField = (string) $levelField;
// 	}
// 	/**
// 	 *
// 	 * @param NN6DataCollection $baseEntry
// 	 * @param array $selectFieldNames
// 	 * @param unknown_type $childrenOnly
// 	 * @return NN6PdoStatement
// 	 */
// 	public function buildSelect(NN6DataCollection $baseEntry = null, array $selectFieldNames = null, $childrenOnly = false) {
// 		$sqlEscTableName = $this->dbh->quoteField($this->tableName);
// 		$sqlEscRootFkField = $this->dbh->quoteField($this->rootFkField);
// 		$sqlEscLftField = $this->dbh->quoteField($this->lftField);
// 		$sqlEscRgtField = $this->dbh->quoteField($this->rgtField);
// 		$sqlEscLevelField = $this->dbh->quoteField($this->levelField);

// 		$sqlSelect = '';
// 		if (sizeof($selectFieldNames)) {
// 			foreach ($selectFieldNames as $fieldName) {
// 				$sqlSelect .= ', node.' . $this->dbh->quoteField($fieldName);
// 			}
// 		} else {
// 			$sqlSelect = ', node.*';
// 		}

// 		$args = array();
// 		$sql = "
// 		SELECT (COUNT(1)-1) AS " . $sqlEscLevelField . $sqlSelect . "
// 		FROM " . $sqlEscTableName . " AS node, " . $sqlEscTableName . " AS parent
// 		WHERE node." . $sqlEscRootFkField . " = parent." . $sqlEscRootFkField . "
// 		AND (node." . $sqlEscLftField . " BETWEEN parent." . $sqlEscLftField . " AND parent." . $sqlEscRgtField . ")";

// 		if (isset($baseEntry)) {
// 			if ($childrenOnly) {
// 				$sql .= "
// 				AND node." . $sqlEscRootFkField . " = :baseRootId
// 				AND node." . $sqlEscLftField . " > :baseLft
// 				AND node." . $sqlEscRgtField . " < :baseRgt
// 				AND parent." . $sqlEscLftField . " > :baseLft
// 				AND parent." . $sqlEscRgtField . " < :baseRgt";
// 			} else {
// 				$sql .= "
// 				AND node." . $sqlEscRootFkField . " = :baseRootId
// 				AND node." . $sqlEscLftField . " >= :baseLft
// 				AND node." . $sqlEscRgtField . " <= :baseRgt
// 				AND parent." . $sqlEscLftField . " >= :baseLft
// 				AND parent." . $sqlEscRgtField . " <= :baseRgt";
// 			}
				
// 			$args[':baseRootId'] = $baseEntry->__get($this->rootFkField);
// 			$args[':baseLft'] = $baseEntry->__get($this->lftField);
// 			$args[':baseRgt'] = $baseEntry->__get($this->rgtField);
// 		}

// 		$sql .= "
// 		GROUP BY node." . $this->dbh->quoteField($this->idFieldName) . "
// 		ORDER BY parent." . $sqlEscRootFkField . " ASC, node." . $sqlEscLftField . " ASC";

// 		$stmt = $this->dbh->prepare($sql);
// 		$stmt->execute($args);

// 		return $stmt;
// 	}
// 	/**
// 	 *
// 	 * @param NN6DataCollection $baseEntry
// 	 * @return NN6PdoStatement
// 	 */
// 	public function buildDelete(NN6DataCollection $baseEntry) {
// 		$sqlEscTableName = $this->dbh->quoteField($this->tableName);
// 		$sqlEscRootFkField = $this->dbh->quoteField($this->rootFkField);
// 		$sqlEscLftField = $this->dbh->quoteField($this->lftField);
// 		$sqlEscRgtField = $this->dbh->quoteField($this->rgtField);

// 		$rootId = $baseEntry->__get($this->rootFkField);
// 		$lft = $baseEntry->__get($this->lftField);
// 		$rgt = $baseEntry->__get($this->rgtField);
// 		$diff = $rgt - $lft + 1;

// 		$stmt = $this->dbh->prepare('
// 				DELETE FROM ' . $sqlEscTableName . '
// 				WHERE ' . $sqlEscRootFkField . ' = ?
// 				AND ' . $sqlEscLftField . ' >= ?
// 				AND ' . $sqlEscRgtField . ' <= ?');
// 		$stmt->execute(array($rootId, $lft, $rgt));
			
// 		$stmt = $this->dbh->prepare('
// 				UPDATE ' . $sqlEscTableName . '
// 				SET ' . $sqlEscLftField . ' = ' . $sqlEscLftField . ' - ?
// 				WHERE ' . $sqlEscRootFkField . ' = ?
// 				AND ' . $sqlEscLftField . ' > ?');
// 		$stmt->execute(array($diff, $rootId, $lft));

// 		$stmt = $this->dbh->prepare('
// 				UPDATE ' . $sqlEscTableName . '
// 				SET ' . $sqlEscRgtField . ' = ' . $sqlEscRgtField . ' - ?
// 				WHERE ' . $sqlEscRootFkField . ' = ?
// 				AND ' . $sqlEscRgtField . ' >= ?');
// 		$stmt->execute(array($diff, $rootId, $rgt));
// 	}
// }

// class NN6NestedSetHelper {
// 	private $dbh;
// 	private $rootFkField;
// 	private $lftField;
// 	private $rgtField;
// 	private $levelField;
// 	/**
// 	 *
// 	 * @param NN6Pdo $dbh
// 	 * @param unknown_type $rootFkField
// 	 * @param unknown_type $lftField
// 	 * @param unknown_type $rgtField
// 	 * @param unknown_type $levelField
// 	 */
// 	public function __construct(NN6Pdo $dbh, $rootFkField = NN6PdoNestedSetStatementBuilder::DEFAULT_ROOT_ID_FIELD,
// 			$lftField = NN6PdoNestedSetStatementBuilder::DEFAULT_LFT_FIELD, $rgtField = NN6PdoNestedSetStatementBuilder::DEFAULT_RGT_FIELD,
// 			$levelField = NN6PdoNestedSetStatementBuilder::DEFAULT_LEVEL_FIELD) {

// 		$this->dbh = $dbh;
// 		$this->rootFkField = $rootFkField;
// 		$this->lftField = $lftField;
// 		$this->rgtField = $rgtField;
// 		$this->levelField = $levelField;
// 	}
// 	/**
// 	 *
// 	 * @param unknown_type $tableName
// 	 * @param unknown_type $idFieldName
// 	 * @return NN6PdoNestedSetStatementBuilder
// 	 */
// 	private function createBuilder($tableName, $idFieldName) {
// 		return new NN6PdoNestedSetStatementBuilder($this->dbh, $tableName, $idFieldName,
// 				$this->rootFkField, $this->lftField, $this->rgtField, $this->levelField);
// 	}
// 	/**
// 	 *
// 	 * @param NN6EntityClass $entityClass
// 	 * @param unknown_type $optionLabelField
// 	 * @param NN6Entity $baseEntry
// 	 * @param unknown_type $optionKeyField
// 	 * @param unknown_type $childrenOnly
// 	 * @return array
// 	 */
// 	public function getOptions(NN6EntityClass $entityClass, $optionLabelField, NN6Entity $baseEntry = null, $optionKeyField = null, $childrenOnly = false) {
// 		$optionLabelField = (string) $optionLabelField;
// 		if (is_null($optionKeyField)) {
// 			$optionKeyField = $entityClass->getIdFieldName();
// 		}

// 		$stmtBuilder = $this->createBuilder($entityClass->getTableName(), $entityClass->getIdFieldName());
// 		$stmt = $stmtBuilder->buildSelect($baseEntry, array($optionLabelField, $optionKeyField), $childrenOnly);

// 		$options = array();
// 		while ($row = $stmt->fetch(NN6Pdo::FETCH_ASSOC)) {
// 			$options[$row[$optionKeyField]] = str_repeat('-', $row[$this->levelField]) . ' ' . $row[$optionLabelField];
// 		}
// 		return $options;
// 	}
// 	/**
// 	 *
// 	 * @param NN6MetaTable $metaTable
// 	 * @param unknown_type $optionLabelField
// 	 * @param NN6DataCollection $baseEntry
// 	 * @param unknown_type $optionKeyField
// 	 * @param unknown_type $childrenOnly
// 	 * @return array
// 	 */
// 	public function getOptionsFromMeta(NN6MetaTable $metaTable, $optionLabelField, NN6DataCollection $baseEntry = null, $optionKeyField = null, $childrenOnly = false) {
// 		$optionLabelField = (string) $optionLabelField;
// 		if (is_null($optionKeyField)) {
// 			$optionKeyField = $metaTable->getPrimaryKeyField()->getName();
// 		}

// 		$stmtBuilder = $this->createBuilder($metaTable->getName(), $metaTable->getPrimaryKeyField()->getName());
// 		$stmt = $stmtBuilder->buildSelect($baseEntry, array($optionLabelField, $optionKeyField), $childrenOnly);

// 		$options = array();
// 		while ($row = $stmt->fetch(NN6Pdo::FETCH_ASSOC)) {
// 			$options[$row[$optionKeyField]] = str_repeat('-', $row[$this->levelField]) . ' ' . $row[$optionLabelField];
// 		}
// 		return $options;
// 	}
// 	/**
// 	 *
// 	 * @param array $dataCollections
// 	 * @param unknown_type $optionLabelFieldName
// 	 * @return array
// 	 */
// 	public function createOptionsFromDataCollections(array $dataCollections, $optionLabelFieldName) {
// 		$options = array();
// 		foreach ($dataCollections as $dataCollection) {
// 			$options[$dataCollection->getId()] = str_repeat('-', $dataCollection->__get($this->levelField)) . ' ' . $dataCollection->__get($optionLabelFieldName);
// 		}
// 		return $options;
// 	}
// 	/**
// 	 *
// 	 * @param NN6EntityClass $entityClass
// 	 * @param NN6Entity $baseEntity
// 	 * @param unknown_type $childrenOnly
// 	 * @return array
// 	 */
// 	public function getEntities(NN6EntityClass $entityClass, NN6Entity $baseEntity = null, $childrenOnly = false) {
// 		$stmtBuilder = $this->createBuilder($entityClass->getTableName(), $entityClass->getIdFieldName());
// 		$stmt = $stmtBuilder->buildSelect($baseEntity, null, $childrenOnly);
// 		return $this->dbh->getEntityManager()->findByStmt($entityClass, $stmt);
// 	}
// 	/**
// 	 *
// 	 * @param NN6MetaTable $metaTable
// 	 * @param NN6TableEntry $baseEntry
// 	 * @param unknown_type $childrenOnly
// 	 * @return array
// 	 */
// 	public function getTableEntries(NN6MetaTable $metaTable, NN6TableEntry $baseEntry = null, $childrenOnly = false) {
// 		$stmtBuilder = $this->createBuilder($metaTable->getName(), $metaTable->getPrimaryKeyField()->getName());
// 		$stmt = $stmtBuilder->buildSelect($baseEntry, null , $childrenOnly);
// 		return $metaTable->findTableEntries($stmt);
// 	}
// 	/**
// 	 *
// 	 * @param NN6MetaTable $metaTable
// 	 * @param NN6TableEntry $dataCollection
// 	 * @return NN6TableEntry
// 	 */
// 	public function getParentTableEntry(NN6MetaTable $metaTable, NN6DataCollection $dataCollection) {
// 		$selector = new NN6Selector();
// 		$selector->add($this->rootFkField, $dataCollection->__get($this->rootFkField));
// 		$selector->add($this->lftField, $dataCollection->__get($this->lftField), NN6Selector::OPERATOR_SMALLER_THAN);
// 		$selector->add($this->rgtField, $dataCollection->__get($this->rgtField), NN6Selector::OPERATOR_LARGER_THAN);

// 		$entries = $metaTable->findTableEntriesByFilter($selector, array($this->lftField => 'DESC'), 1);
// 		if (sizeof($entries)) {
// 			return current($entries);
// 		}

// 		return null;
// 	}
// 	/**
// 	 *
// 	 * @param NN6MetaTable $metaTable
// 	 * @param NN6TableEntry $dataCollection
// 	 * @return NN6TableEntry
// 	 */
// 	public function moveTableEntry(NN6MetaTable $metaTable, NN6DataCollection $ds, $parentEntryId) {
// 		$dbh = $metaTable->getPdo();
// 		$dbh->beginTransactionIna();

// 		// if the old branch has a parent, move the branch to root (this is always the first action!)
// 		if ($ds->getId() != $ds->__get($this->rootFkField)) {
// 			$this->moveTableEntryToRoot($metaTable, $ds);
// 		}

// 		$parentDs = null;

// 		if (is_null($parentEntryId) || !($parentDs = $metaTable->findTableEntry($parentEntryId))) {
// 			$dbh->commitIna();
// 			return;
// 		}

// 		$numChilds = intval(($ds->__get($this->rgtField) - $ds->__get($this->lftField)) / 2);
			
// 		// makes rgt space in new parent
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET rgt = rgt + ?
// 				WHERE rgt >= ? AND root_id = ?');
// 		$stmt->execute(array(($numChilds + 1) * 2, $parentDs->__get($this->rgtField),
// 				$parentDs->__get($this->rootFkField)));

// 		// makes lft space in new parent
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET lft = lft + ?
// 				WHERE lft > ? AND root_id = ?');
// 		$stmt->execute(array(($numChilds + 1) * 2, $parentDs->__get($this->rgtField),
// 				$parentDs->__get($this->rootFkField)));
			
// 		// moves all elements into the new parent
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET lft = lft + ?, rgt = rgt + ?, root_id = ?
// 				WHERE root_id = ?');
// 		$stmt->execute(array($parentDs->__get($this->rgtField) - 1, $parentDs->__get($this->rgtField) - 1,
// 				$parentDs->__get($this->rootFkField), $ds->getId()));

// 		$dbh->commitIna();
// 	}
// 	/**
// 	 * Disconnects an existing branch and creates new root-branch
// 	 * @param NN6MetaTable $metaTable
// 	 * @param NN6DataCollection $ds
// 	 */
// 	private function moveTableEntryToRoot(NN6MetaTable $metaTable, NN6DataCollection $ds){
// 		$dbh = $metaTable->getPdo();
// 		$numChilds = intval(($ds->__get($this->rgtField) - $ds->__get($this->lftField)) / 2);

// 		// sets the new root_id and the corrected lft and rgt value of the moved entries
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET lft = lft - ?, rgt = rgt - ?, root_id = ?
// 			 WHERE root_id = ? AND lft BETWEEN ? AND ?');
// 		$stmt->execute(array($ds->__get($this->lftField) - 1, $ds->__get($this->lftField) - 1, $ds->getId(),
// 				$ds->__get($this->rootFkField), $ds->__get($this->lftField), $ds->__get($this->rgtField)));

// 		// sets the correct lft of the remaining elements
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET lft = lft - ?
// 			 WHERE lft > ? AND root_id = ?');
// 		$stmt->execute(array(($numChilds + 1) * 2, $ds->__get($this->rgtField), $ds->__get($this->rootFkField)));

// 		// sets the correct rgt of the remaining elements
// 		$stmt = $dbh->prepare('
// 				UPDATE ' . $dbh->quoteField($metaTable->getName()) . '
// 				SET rgt = rgt - ?
// 			 WHERE rgt > ? AND root_id = ?');
// 		$stmt->execute(array(($numChilds + 1) * 2, $ds->__get($this->rgtField),
// 				$ds->__get($this->rootFkField)));
// 	}
// }

class UnknownEntryException extends OrmException {
	
}
