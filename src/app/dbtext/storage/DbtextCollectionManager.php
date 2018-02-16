<?php
namespace dbtext\storage;

use dbtext\config\DbtextConfig;
use dbtext\exception\CachingException;
use dbtext\exception\CorruptedCacheException;
use n2n\context\RequestScoped;
use n2n\core\container\N2nContext;
use n2n\core\container\TransactionManager;
use n2n\util\cache\CacheStore;
use n2n\util\cache\CorruptedCacheStoreException;

class DbtextCollectionManager implements RequestScoped, GroupDataListener {
	const NS = 'dbtext';
	const APP_CACHE_PREFIX = 'dbtext_group_data_';

	/**
	 * @var GroupData[] $groupDatas
	 */
	private $groupDatas;
	/**
	 * @var DbtextDao $dbtextDao
	 */
	private $dbtextDao;
	/**
	 * @var DbtextConfig $moduleConfig
	 */
	private $moduleConfig;
	/**
	 * @var CacheStore $cacheStore
	 */
	private $cacheStore;

	private function _init(DbtextDao $dbtextDao, N2nContext $n2nContext) {
		$this->dbtextDao = $dbtextDao;
		$this->moduleConfig = $n2nContext->getModuleConfig(self::NS);
		$this->cacheStore = $n2nContext->getAppCache()->lookupCacheStore(DbtextCollectionManager::class);
	}

	/**
	 * Finds GroupData by
	 *
	 * @param string $namespace
	 * @return GroupData
	 */
	public function getGroupData(string $namespace) {
		if (isset($this->groupDatas[$namespace])) {
			return $this->groupDatas[$namespace];
		}

		$this->groupDatas[$namespace] = $this->readCachedGroupData($namespace);
		if ($this->groupDatas[$namespace] === null) {
			$this->groupDatas[$namespace] = $this->dbtextDao->getGroupData($namespace);
			$this->writeToAppCache($this->groupDatas[$namespace]);
		}

		if ($this->moduleConfig->isCreateOnRequest()) {
			$this->groupDatas[$namespace]->registerListener($this);
		}

		return $this->groupDatas[$namespace];
	}

	private function readCachedGroupData(string $namespace) {
		$groupData = null;
		try {
			$cacheItem = $this->cacheStore->get(self::APP_CACHE_PREFIX . $namespace, array());
			if ($cacheItem === null) return null;

			if ($cacheItem->data instanceof GroupData) {
				return $cacheItem->data;
			}
		} catch (CorruptedCacheStoreException $e) {
		}

		$this->clearCache($namespace);
		return null;
	}

	/**
	 * @param GroupData $groupData
	 */
	private function writeToAppCache(GroupData $groupData) {
		if (!empty($groupData->getListeners())) {
			throw new \InvalidArgumentException('GroupData cannot have registered listeners while caching');
		}
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
		$this->dbtextDao->insertId($groupData->getNamespace(), $id);
		$this->clearCache($groupData->getNamespace());
	}
}