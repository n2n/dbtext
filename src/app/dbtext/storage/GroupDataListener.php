<?php
namespace dbtext\storage;

use dbtext\text\Text;

interface GroupDataListener {
	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @param array|null $args
	 */
	public function keyAdded(string $key, GroupData $groupData);

	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @param array $args
	 */
	public function placeholdersChanged(string $key, string $ns, array $newArgs = null);
}