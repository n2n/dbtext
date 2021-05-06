<?php
namespace phpbob;

abstract class PhpStatementAdapter implements PhpStatement {
	
	private $code = null;
	private $prependingCommentLines = null;
	
	public function getLines(): array {
		return preg_split('/(\\r\\n|\\n|\\r)/', (string) $this);
	}
	
	public function getCode(): string {
		if (null === $this->code) {
			$this->determineLines();
		}
		
		return $this->code;
	}
	
	public function getPrependingCommentLines(): array {
		if (null === $this->prependingCommentLines) {
			$this->determineLines();
		}
		
		return $this->prependingCommentLines;
	}
	
	private function determineLines() {
		$this->code = '';
		$this->prependingCommentLines = array();
		$inComment = false;
		
		foreach ($this->getLines() as $line) {
			if (empty($this->code)) {
				if ($inComment) {
					if ($this->hasCommentEnd($line)) {
						$this->applyCodeLine($line);
						$inComment = false;
						continue;
					}
					
					$this->prependingCommentLines[] = $line;
					continue;
				}
				
				if (preg_match('/(^\s*$|^\s*(\/\/|#))/', $line)) {
					$this->prependingCommentLines[] = $line;
					continue;
				}
				
				if (preg_match('/^\s*\/\*/', $line)) {
					if (!$this->hasCommentEnd($line)) {
						$inComment = true;
						$this->prependingCommentLines[] = $line;
						continue;
					}
					$this->applyCodeLine($line);
					continue;
				}
			}
			
			$this->applyCodeLine($line);
		}
	}

	private function hasCommentStart($string) {
		return preg_match('/\/\*/', $string);
	}

	private function hasCommentEnd($string) {
		return preg_match('/\*\//', $string);
	}
	
	private function applyCodeLine(string $line) {
		//replace tailing Comments
		$line = $this->replaceTrailingComments($line);
		
		$lineParts = preg_split('/(\/\*|\*\/)/', $line, null, PREG_SPLIT_DELIM_CAPTURE);
		if (count($lineParts) > 1) {
			$lineStr = '';
			
			$tStr = '';
			$inComment = null;
			
			foreach ($lineParts as $linePart) {
				if ($this->hasCommentEnd($linePart)) {
					if (empty($this->code)) {
						//this is the last prepending code line
						$this->prependingCommentLines[] = $tStr . $linePart;
					}
					$tStr = null;
					$inComment = false;
					continue;
				}
				
				if ($this->hasCommentStart($linePart)) {
					$lineStr .= $tStr;
					$inComment = true;
					continue;
				}
				
				$tStr .= $linePart;
			}
			if (!$inComment) {
				$lineStr .= $tStr;
			}
			
			$line = $lineStr;
		}
		
		if (!empty($this->code)) {
			$this->code .= ' ';
		}
 		$this->code .= $line;
	}
	
	private function replaceTrailingComments(string $str) {
		if (!preg_match('/(\/\/|#).*$/', $str)) return $str;
		$inStr = null;
		$firstSlashDetected = false;
		
		$newStr = '';
		foreach (str_split($str) as $s) {
			if (null !== $inStr) {
				if ($inStr === $s) {
					$inStr = null;
				}
				
				$newStr .= $s;
				continue;
			}
			
			if ($s === '/') {
				if ($firstSlashDetected) break;
				
				$firstSlashDetected = true;
				continue;
			}
			
			if ($s === '#') break;
			
			if ($s === '\'' || $s === '"') {
				$inStr = $s;
			}
			
			$newStr .= $s;

		}
		
		return $newStr;
	}
}
