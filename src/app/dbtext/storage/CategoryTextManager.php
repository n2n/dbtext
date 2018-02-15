<?php
namespace dbtext\storage;

use dbtext\config\DbTextConfig;
use n2n\context\RequestScoped;
use n2n\core\container\AppCache;
use n2n\core\container\N2nContext;
use n2n\core\N2N;
use n2n\util\cache\CacheStore;

class CategoryTextManager implements RequestScoped, CategoryDataListener {
	const APP_CACHE_PREFIX = 'dbtext_category_data_';

	/**
	 * @var CategoryData[] $categoryDatas
	 */
	private $categoryDatas;
	/**
	 * @var CategoryTextDao $categoryTextDao
	 */
	private $categoryTextDao;
	/**
	 * @var DbTextConfig $moduleConfig
	 */
	private $moduleConfig;
	/**
	 * @var CacheStore $cacheStore
	 */
	private $cacheStore;

	private function _init(CategoryTextDao $categoryTextDao, N2nContext $n2nContext) {
		$this->categoryTextDao = $categoryTextDao;
		$this->moduleConfig = $n2nContext->getModuleConfig('dbtext');
		$this->cacheStore = $n2nContext->getAppCache()->lookupCacheStore('dbtext');
	}

	/**
	 * @param string $namespace
	 * @return CategoryData
	 */
	public function getCategoryData(string $namespace): CategoryData {
		if ($this->categoryDatas[$namespace] !== null) {
			return $this->categoryDatas[$namespace];
		}

		$cachedCategoryItem = $this->cacheStore->get(self::APP_CACHE_PREFIX . $namespace, array());
		if (null !== $cachedCategoryItem) {
			$this->categoryDatas[$namespace] = $cachedCategoryItem->data;
		}

		if (null === $this->categoryDatas[$namespace]) {
			$this->categoryDatas[$namespace] = $this->categoryTextDao->getCategoryData($namespace);
			$this->writeToAppCache($this->categoryDatas[$namespace]);
		}

		if ($this->moduleConfig->isCreateOnRequest()) {
			$this->categoryDatas[$namespace]->registerListener($this);
		}

		return $this->categoryDatas[$namespace];
	}

	/**
	 * @param CategoryData $categoryData
	 */
	private function writeToAppCache(CategoryData $categoryData) {
		$this->cacheStore->store(self::APP_CACHE_PREFIX . $categoryData->getNamespace(), array(), $categoryData);
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

	public function idAdded(string $id, CategoryData $categoryData) {
		$this->categoryTextDao->insertId($categoryData->getNamespace(), $id);
		$this->clearCache($categoryData->getNamespace());
	}
}