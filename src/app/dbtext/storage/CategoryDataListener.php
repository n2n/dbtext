<?php
namespace dbtext\storage;

interface CategoryDataListener {
	/**
	 * @param string $id
	 * @param CategoryData $categoryData
	 * @return mixed
	 */
	public function idAdded(string $id, CategoryData $categoryData);
}