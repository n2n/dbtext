<?php
namespace rocket\si\content\impl\iframe;

use rocket\si\content\impl\OutSiFieldAdapter;

class IframeOutSiField extends OutSiFieldAdapter {
	private $iframeData;
	
	public function __construct(IframeData $iframeData) {
		$this->iframeData = $iframeData;
	}

	/**
	 * @return string
	 */
	function getType(): string {
		return 'iframe-out';
	}

	/**
	 * @return array
	 * @throws \n2n\util\magic\MagicObjectUnavailableException
	 */
	function getData(): array {
		return $this->iframeData->toArray();
	}
}