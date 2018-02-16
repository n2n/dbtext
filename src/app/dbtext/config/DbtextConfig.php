<?php
namespace dbtext\config;

class DbtextConfig {
	private $createOnRequest = true;

	/**
	 * If true when requesting a {@see Text} that doesn't exist yet it will be created.
	 *
	 * @return boolean
	 */
	public function isCreateOnRequest(): bool {
		return $this->createOnRequest;
	}

	/**
	 * If set to true, requested {@see Text Texts} that don't exist yet will be created.
	 *
	 * @param bool $createOnRequest
	 */
	public function setCreateOnRequest(bool $createOnRequest) {
		$this->createOnRequest = $createOnRequest;
	}
}