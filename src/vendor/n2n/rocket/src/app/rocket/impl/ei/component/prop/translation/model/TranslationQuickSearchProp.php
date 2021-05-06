<?php
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\compare\ComparatorCriteria;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;

class TranslationQuickSearchProp implements QuickSearchProp {
	private $entityProperty;
	private $targetEntityClass;
	/**
	 * @var QuickSearchDefinition $targetQuickSearchDefinition
	 */
	private $targetQuickSearchDefinition;
	
	public function __construct(EntityProperty $entityProperty, \ReflectionClass $targetEntityClass, QuickSearchDefinition $targetQuickSearchDefinition) {
		$this->entityProperty = $entityProperty;
		$this->targetEntityClass = $targetEntityClass;
		$this->targetQuickSearchDefinition = $targetQuickSearchDefinition;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\quick\QuickSearchProp::createComparatorConstraint()
	 */
	public function buildComparatorConstraint(string $queryStr): ComparatorConstraint {
		return new TranslationComparatorConstraint($this->entityProperty, $this->targetEntityClass, 
				$this->targetQuickSearchDefinition->buildCriteriaConstraint($queryStr));
	}
}

class TranslationComparatorConstraint implements ComparatorConstraint {
	private $entityProperty;
	private $targetEntityClass;
	private $targetComparatorConstraint;
	
	public function __construct(EntityProperty $entityProperty, \ReflectionClass $targetEntityClass, 
			ComparatorConstraint $targetComparatorConstraint = null) {
		$this->entityProperty = $entityProperty;
		$this->targetEntityClass = $targetEntityClass;
		$this->targetComparatorConstraint = $targetComparatorConstraint;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias) {
		if ($this->targetComparatorConstraint === null) return;
		
		$critProp = CrIt::p($alias, $this->entityProperty);
		$subAlias = $criteriaComparator->endClause()->uniqueAlias();
		
		$subCriteria = new ComparatorCriteria();
		$subCriteria->select($subAlias)->from($this->targetEntityClass, $subAlias);
		
		$this->targetComparatorConstraint->applyToCriteriaComparator($subCriteria->where(), CrIt::p($subAlias));
		
		$criteriaComparator->match($critProp, 'CONTAINS ANY', $subCriteria);
	}
}