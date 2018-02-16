<?php
namespace dbtext\storage;

use dbtext\config\DbTextConfig;
use n2n\context\RequestScoped;
use n2n\core\container\N2nContext;
use n2n\util\cache\CacheStore;

class DbTextCollectionManager implements RequestScoped, GroupDataListener {
	const APP_CACHE_PREFIX = 'dbtext_group_data_';

	/**
	 * @var GroupData[] $groupDatas
	 */
	private $groupDatas;
	/**
	 * @var DbTextDao $dbTextDao
	 */
	private $dbTextDao;
	/**
	 * @var DbTextConfig $moduleConfig
	 */
	private $moduleConfig;
	/**
	 * @var CacheStore $cacheStore
	 */
	private $cacheStore;

	private function _init(DbTextDao $dbTextDao, N2nContext $n2nContext) {
		$this->dbTextDao = $dbTextDao;
		$this->moduleConfig = $n2nContext->getModuleConfig('dbtext');
		$this->cacheStore = $n2nContext->getAppCache()->lookupCacheStore('dbtext');
	}

	/**
	 * Finds GroupData by
	 *
	 * @param string $namespace
	 * @return GroupData
	 */
	public function getGroupData(string $namespace): GroupData {
		if ($this->groupDatas[$namespace] !== null) {
			return $this->groupDatas[$namespace];
		}

		$cachedGroupItem = $this->cacheStore->get(self::APP_CACHE_PREFIX . $namespace, array());
		if (null !== $cachedGroupItem) {
			$this->groupDatas[$namespace] = $cachedGroupItem->data;
		}

		if (null === $this->groupDatas[$namespace]) {
			$this->groupDatas[$namespace] = $this->dbTextDao->getGroupData($namespace);
			$this->writeToAppCache($this->groupDatas[$namespace]);
		}

		if ($this->moduleConfig->isCreateOnRequest()) {
			$this->groupDatas[$namespace]->registerListener($this);
		}

		return $this->groupDatas[$namespace];
	}

	/**
	 * @param GroupData $groupData
	 */
	private function writeToAppCache(GroupData $groupData) {
		$this->cacheStore->store(self::APP_CACHE_PREFIX . $groupData->getNamespace(), array(), $groupData);
	}

	/**
	 * If no namespace is given, the whole dbtext cache is cleared.
	 * @param string $namespace
	 */
	public function clearCache(string $namespace = null) {
		if (null !== $namespace) {
			$this->cacheStore->remove(self::APP_CACHE_PREFIX . $namespace, array());
			return;
		}

		$this->cacheStore->clear();
	}

	/**
	 * @param string $id
	 * @param GroupData $groupData
	 */
	public function idAdded(string $id, GroupData $groupData) {
		$this->dbTextDao->insertId($groupData->getNamespace(), $id);
		$this->clearCache($groupData->getNamespace());
	}
}