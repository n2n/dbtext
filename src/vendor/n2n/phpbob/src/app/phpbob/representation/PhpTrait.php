<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpTrait extends PhpClassLikeAdapter {
	public function __toString() {
		return $this->getPrependingString() . Phpbob::KEYWORD_TRAIT . ' ' . $this->getName() . ' ' 
				. Phpbob::GROUP_STATEMENT_OPEN . PHP_EOL . $this->generateBody() . Phpbob::GROUP_STATEMENT_CLOSE;
	}
}