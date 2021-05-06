<?php
namespace phpbob\analyze;

use phpbob\StatementGroup;
use phpbob\SingleStatement;
use phpbob\Phpbob;
use n2n\reflection\annotation\AnnotationSet;
use phpbob\representation\PhpFile;

class PhpSourceAnalyzer {
	/**
	 * @param string $phpSource
	 * @param AnnotationSet $as
	 * @return PhpFile
	 */
	public function analyze($phpSource/* , AnnotationSet $as = null */) {
		
		$rootGroup = $this->createPhpStatement($phpSource);
		
		$phpFileBuilder = new PhpFileBuilder();
		
		foreach ($rootGroup->getChildPhpStatements() as $phpStatement) {
			$phpFileBuilder->processPhpStatement($phpStatement);
		}
		
		return $phpFileBuilder->getPhpFile();
	}

	/**
	 * @throws PhpSourceAnalyzingException
	 * @return \phpbob\StatementGroup
	 */
	private function createPhpStatement($phpSource) {

		$content = null;
		$rootGroup = new StatementGroup(null);
		$currentGroup = $rootGroup;
		$groupStack = array();
		$startAnalyzing = false;

		$inString = false;
		// ' or "
		$stringStartToken = null;
		
		foreach (token_get_all($phpSource) as $token) {
			if (!$startAnalyzing) {
				if (!is_long($token[0])) continue;
				if ($token[0] === T_CLOSE_TAG) {
					throw new PhpSourceAnalyzingException('Too many PHP-end-tags (?>)');
				}
				
				if ($token[0] === T_OPEN_TAG) {
					$startAnalyzing = true;
					continue;
				}
			}
			
			if ($token[0] === T_CLOSE_TAG) {
				$startAnalyzing = false;
				continue;
			}
			
			if ($token[0] === T_OPEN_TAG) {
				throw new PhpSourceAnalyzingException('Too many PHP opening tags (<?php|<?=|<?)');
				continue;
			}
			
			if (is_long($token[0])) {
				$content .= $token[1];
				continue;
			}
			
			if ($inString) {
				$content .= $token[0];
				if ($token[0] === $stringStartToken) {
					$inString = false;
				}
				continue;
			}
			
			if ($token[0] === "'" || $token[0] === '"') {
				$inString = true;
				$content .= $token[0];
				$stringStartToken = $token[0];
				continue;
			}
			
			switch ($token[0]) {
				case Phpbob::SINGLE_STATEMENT_STOP:
					$currentGroup->addChildPhpStatement(new SingleStatement($content));
					$content = null;
					break;
						
				case Phpbob::GROUP_STATEMENT_OPEN:
					$newGroup = new StatementGroup($content);
					$currentGroup->addChildPhpStatement($newGroup);
					$groupStack[] = $currentGroup;
					$currentGroup = $newGroup;
					$content = null;
					break;
						
				case Phpbob::GROUP_STATEMENT_CLOSE:
					if (count($groupStack) === 0) {
						throw new PhpSourceAnalyzingException('Invalid PHP File asdasdf');
					}
						
					$currentGroup->setEndCode($content);
					if ('' !== trim($content)) {
						/*throw new PhpSourceAnalyzingException('Invalid PHP File');*/
					}
					$content = null;
			
					$currentGroup = array_pop($groupStack);
					break;
				default:
					$content .= $token[0];
			}
		}
		
		return $rootGroup;
	}
}