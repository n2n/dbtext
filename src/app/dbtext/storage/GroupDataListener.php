<?php
namespace dbtext\storage;

interface GroupDataListener {
	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @return mixed
	 */
	public function keyAdded(string $key, GroupData $groupData);
}