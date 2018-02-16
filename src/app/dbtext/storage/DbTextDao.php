<?php
namespace dbtext\storage;

use dbtext\text\Group;
use dbtext\text\Text;
use n2n\context\RequestScoped;
use n2n\core\container\TransactionManager;
use n2n\persistence\orm\EntityManager;

class DbTextDao implements RequestScoped {
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
		$group = $this->getCategory($namespace);
		$text = new Text($id, $group);
		$texts = (array) $group->getTexts();
		array_push($texts, $text);
		$group->setTexts($texts);
		$t = $this->tm->createTransaction();
		$this->em->persist($text);
		$t->commit();
	}

	/**
	 * @param string $namespace
	 */
	public function getGroupData(string $namespace) {
		$result = $this->em->createNqlCriteria('SELECT  t.id, t.textTs.n2nLocale, t.textTs.str 
				FROM Text t 
				WHERE t.group.namespace = :ns',
				array('ns' => $namespace))->toQuery()->fetchArray();

		if (count($result) === 0) {
			return new GroupData($namespace);
		}

		return new GroupData($namespace, $this->formGroupDataResult($result));
	}

	/**
	 * Gets group if exists.
	 * If Category does not exist a new one is created.
	 *
	 * @param string $namespace
	 * @return Group
	 */
	private function getCategory(string $namespace): Group {
		$group = $this->em->createSimpleCriteria(Group::getClass(), array('namespace' => $namespace))
				->toQuery()->fetchSingle();

		if (null !== $group) {
			return $group;
		}

		$group = new Group($namespace);
		$t = $this->tm->createTransaction();
		$this->em->persist($group);
		$t->commit();
		return $group;
	}

	/**
	 * @param array $result
	 * @return array
	 */
	private function formGroupDataResult(array $result) {
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