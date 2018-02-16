<?php
namespace dbtext\storage;

interface GroupDataListener {
	/**
	 * @param string $id
	 * @param GroupData $groupData
	 * @return mixed
	 */
	public function idAdded(string $id, GroupData $groupData);
}