<?php
namespace rocket\ei\manage\gui;

interface EiGuiSiFactory {
	
	/**
	 * @return \rocket\si\meta\SiStructureDeclaration
	 */
	public function getSiStructureDeclarations(): array;
}
