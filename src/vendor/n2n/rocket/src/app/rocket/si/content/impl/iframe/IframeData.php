<?php
namespace rocket\si\content\impl\iframe;

use n2n\util\uri\Url;
use n2n\web\ui\UiComponent;
use n2n\core\container\N2nContext;
use n2n\web\ui\SimpleBuildContext;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;

class IframeData {
	private $url;
	private $srcDoc;

	/**
	 * @param string|null $url
	 * @param string|null $srcDoc
	 */
	private function __construct(string $url = null, string $srcDoc = null) {
		$this->url = $url;
		$this->srcDoc = $srcDoc;
	}
	
	static function createFromUrl(Url $url) {
		return new IframeData((string) $url);
	}
	 
	static function createFromUiComponent(UiComponent $uiComponent) {
		return new IframeData(null, $uiComponent->build(new SimpleBuildContext()));
	}
	
	static function createFromUiComponentWithTemplate(UiComponent $uiComponent, N2nContext $n2nContext) {
		$viewFactory = $n2nContext->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		$view = $viewFactory->create('rocket\si\content\impl\iframe\view\iframeTemplate.html',
				['uiComponent' => $uiComponent]);

		return new IframeData(null, $view->getContents());
	}
	
	function toArray() {
		return [
			'url' => $this->url,
			'srcDoc' => $this->srcDoc
		];
	}

}
