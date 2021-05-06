<?php
namespace n2n\composer\module;

use Composer\Plugin\PluginInterface;
use Composer\IO\IOInterface;

class ModulePlugin implements PluginInterface {
	
	public function activate(\Composer\Composer $composer, IOInterface $io) {
		$installer = new ModuleInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);
	}
}