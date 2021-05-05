<?php
namespace dbtext\storage;

interface GroupDataListener {
	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @param array|null $args
	 */
	public function keyAdded(string $key, GroupData $groupData, array $args);

	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @param array $args
	 */
	public function placeholdersChanged(string $key, string $ns, array $newArgs = null);
}
