<?php
namespace rocket\impl\ei\component\prop\relation\model\filter;

use rocket\ei\manage\critmod\quick\QuickSearchProp;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CrIt;

class ToOneQuickSearchProp implements QuickSearchProp {
	private $relationModel;
	private $targetDefPropPaths;
	/**
	 * @var Eiu $targetEiu
	 */
	private $targetEiu;
	
	public function __construct(RelationModel $relationModel, array $targetDefPropPaths, Eiu $targetEiu) {
		$this->relationModel = $relationModel;
		$this->targetDefPropPaths = $targetDefPropPaths;
		$this->targetEiu = $targetEiu;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\quick\QuickSearchProp::createComparatorConstraint()
	 */
	public function buildComparatorConstraint(string $queryStr): ?ComparatorConstraint {
		$entityProperty = $this->relationModel->getRelationEntityProperty();
		$targetComparatorContraint = $this->targetEiu->frame()->getEiFrame()->getQuickSearchDefinition()
				->buildCriteriaConstraint($queryStr, $this->targetDefPropPaths);
		
		if ($targetComparatorContraint === null) {
			return null;
		}
		
		return new ToOneComparatorConstraint($entityProperty, $targetComparatorContraint);
	}
}

class ToOneComparatorConstraint implements ComparatorConstraint {
	private $entityProperty;
	private $targetComparatorConstraint;
		
	public function __construct(EntityProperty $entityProperty, 
			ComparatorConstraint $targetComparatorConstraint) {
		$this->entityProperty = $entityProperty;
		$this->targetComparatorConstraint = $targetComparatorConstraint;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias) {
		$this->targetComparatorConstraint->applyToCriteriaComparator($criteriaComparator, 
				CrIt::p($alias, $this->entityProperty));
	}
}
