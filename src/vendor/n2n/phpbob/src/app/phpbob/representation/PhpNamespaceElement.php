<?php
namespace phpbob\representation;

interface PhpNamespaceElement extends PhpFileElement {
	public function getPhpNamespace();
}