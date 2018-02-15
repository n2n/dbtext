<?php
namespace dbtext\storage;

use dbtext\text\Category;
use dbtext\text\Text;
use n2n\context\RequestScoped;
use n2n\core\container\TransactionManager;
use n2n\persistence\orm\EntityManager;

class CategoryTextDao implements RequestScoped {
	/**
	 * @var EntityManager $em;
	 */
	private $em;
	/**
	 * @var TransactionManager $tm
	 */
	private $tm;

	/**
	 * @param EntityManager $em
	 */
	private function _init(EntityManager $em, TransactionManager $tm) {
		$this->em = $em;
		$this->tm = $tm;
	}

	/**
	 * @param string $namespace
	 * @param string $id
	 */
	public function insertId(string $namespace, string $id) {
		$category = $this->getCategory($namespace);
		$text = new Text($id, $category);
		$texts = (array) $category->getTexts();
		array_push($texts, $text);
		$category->setTexts($texts);
		$t = $this->tm->createTransaction();
		$this->em->persist($text);
		$t->commit();
	}

	/**
	 * @param string $namespace
	 */
	public function getCategoryData(string $namespace) {
		$result = $this->em->createNqlCriteria('SELECT  t.id, t.textTs.n2nLocale, t.textTs.str 
				FROM Text t 
				WHERE t.category.namespace = :ns',
				array('ns' => $namespace))->toQuery()->fetchArray();

		if (count($result) === 0) {
			return new CategoryData($namespace);
		}

		return new CategoryData($namespace, $this->formCategoryDataResult($result));
	}

	/**
	 * Gets category if exists.
	 * If Category does not exist a new one is created.
	 *
	 * @param string $namespace
	 * @return Category
	 */
	private function getCategory(string $namespace): Category {
		$category = $this->em->createSimpleCriteria(Category::getClass(), array('namespace' => $namespace))
				->toQuery()->fetchSingle();

		if (null !== $category) {
			return $category;
		}

		$category = new Category($namespace);
		$t = $this->tm->createTransaction();
		$this->em->persist($category);
		$t->commit();
		return $category;
	}

	/**
	 * @param array $result
	 * @return array
	 */
	private function formCategoryDataResult(array $result) {
		$formedResult = array();

		foreach ($result as $i => $item) {
			if (!isset($formedResult[$item[0]])) {
				$formedResult[$item[0]] = array();
			}

			if ($item[1] === null && $item[2] === null) continue;
			$formedResult[$item[0]][(string) $item[1]] = $item[2];
		}

		return $formedResult;
	}
}