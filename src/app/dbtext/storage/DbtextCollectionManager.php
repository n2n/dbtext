<?php
namespace dbtext\storage;

use dbtext\config\DbtextConfig;
use n2n\context\RequestScoped;
use n2n\core\container\N2nContext;
use n2n\core\N2N;
use n2n\util\cache\CacheStore;
use n2n\util\cache\CorruptedCacheStoreException;

/**
 * Manages data for dbtext module.
 */
class DbtextCollectionManager implements RequestScoped, GroupDataListener {
	const NS = 'dbtext';
	const APP_CACHE_PREFIX = DbtextCollectionManager::class . '_';

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
	 * {@see GroupData} stored in cache or database can be found.
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
		
		if ($this->moduleConfig->isModifyOnRequest()) {
			$this->groupDatas[$namespace]->registerListener($this);
		}

		return $this->groupDatas[$namespace];
	}

	/**
	 * If no namespace is provided, the whole dbtext cache is cleared.
	 * 
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
	 * Adds a {@see Text} to {@see Group} then clears cache for {@see Group::$groupdata::namespace}.
	 * 
	 * @param string $key
	 * @param GroupData $groupData
	 */
	public function keyAdded(string $key, GroupData $groupData, array $args = null) {
		$this->dbtextDao->insertKey($groupData->getNamespace(), $key, $args);
		$this->clearCache($groupData->getNamespace());
	}

	public function placeholdersChanged(string $key, string $ns, array $args = null) {
		$this->dbtextDao->changePlaceholders($key, $ns, $args);
	}

	/**
	 * Finds cached {@see GroupData} by given namespace.
	 * If dbtext cache of namespace is corrupt it is cleared.
	 *
	 * @param string $namespace
	 * @return GroupData|mixed|null
	 */
	private function readCachedGroupData(string $namespace) {
		// Due to confusion, no cached items are returned during development
		if (N2N::isDevelopmentModeOn()) return null;

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
}