<?php
namespace dbtext\storage;

interface GroupDataListener {
	/**
	 * @param string $key
	 * @param GroupData $groupData
	 * @return mixed
	 */
	public function idAdded(string $key, GroupData $groupData);
}