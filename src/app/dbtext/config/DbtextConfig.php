<?php
namespace dbtext\config;

class DbtextConfig {
	private $modifyOnRequest = true;

	/**
	 * If true when requesting a {@see Text} that doesn't exist yet it will be created.
	 *
	 * @return boolean
	 */
	public function isModifyOnRequest(): bool {
		return $this->modifyOnRequest;
	}

	/**
	 * If set to true, requested {@see Text Texts} that don't exist yet will be created.
	 *
	 * @param bool $modifyOnRequest
	 */
	public function setModifyOnRequest(bool $modifyOnRequest) {
		$this->modifyOnRequest = $modifyOnRequest;
	}
}