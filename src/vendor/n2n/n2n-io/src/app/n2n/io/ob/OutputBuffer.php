<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\io\ob;

use n2n\util\StringUtils;
use n2n\util\ex\NotYetImplementedException;

class OutputBuffer {
	const DEFAULT_CHUNK_SIZE = null; // 131072
	private $sealed = false;
	
	private $obLevel = null;
	private $contents = '';
	private $breakPoints = array();

	/**
	 * 
	 * @throws OutputBufferDisturbedException
	 */
	public function start($chunkSize = self::DEFAULT_CHUNK_SIZE) {
		if ($this->isBuffering()) {
			throw new OutputBufferDisturbedException('Output buffer was already started. Buffer level: ' 
					. $this->obLevel);
		}
		
		if ($this->isSealed()) {
			throw new OutputBufferDisturbedException('Output buffer was sealed. Buffer level: ' . $this->obLevel);
		}
		
		ob_start(null, $chunkSize);
		$this->obLevel = ob_get_level();
	}
	
	public function append($contents) {
		$buffering = $this->isBuffering();
		if ($buffering) $this->end();
		
		$this->contents .= $contents;
		
		if ($buffering) $this->start();
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isBuffering() {
		return isset($this->obLevel);
	}
	
	private function prepareBufferAccess($closeChildBuffers) {
		if ($this->obLevel == ob_get_level()) {
			return '';
		}
		
		if (!$closeChildBuffers || $this->obLevel > ob_get_level()) {
			throw new OutputBufferDisturbedException('Output buffering was disturbed. Buffer level: ' . $this->obLevel 
					. ', Current level: ' . ob_get_level());
		}
		
		$output = '';
		for ($level = ob_get_level(); $level > $this->obLevel; $level--) {
			$output = ob_get_contents() . $output;
			ob_end_clean();
		}
		
		return $output;
	}
	/**
	 * 
	 * @throws OutputBufferDisturbedException
	 */
	public function clean($closeChildBuffers = true) {
		if ($this->isBuffering()) {
			$this->prepareBufferAccess($closeChildBuffers);
			
			ob_clean();
		}
		
		$this->contents = '';
	}
	/**
	 *
	 * @throws OutputBufferDisturbedException
	 */
	public function end($closeChildBuffers = true) {
		if (!$this->isBuffering()) return;
		
		$this->contents .= $this->prepareBufferAccess($closeChildBuffers);
		
		$this->contents .= ob_get_contents();
		ob_end_clean();
		$this->obLevel = null;
	}
	
	public function executeOnContinue(\Closure $closure) {
		throw new NotYetImplementedException();
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isSealed() {
		return $this->sealed;
	}
	/**
	 * 
	 */
	public function seal() {
		if ($this->isBuffering()) $this->end();
		
		$this->sealed = true;
	}
	/**
	 * 
	 * @param string $key
	 */
	public function breakPoint($key = null) {
		$buffering = $this->isBuffering();
		if ($buffering) $this->end();
		
		if ($key === null) {
			$this->breakPoints[] = mb_strlen($this->contents);
			end($this->breakPoints);
			$key = key($this->breakPoints);
		} else {
			$this->breakPoints[$key] = mb_strlen($this->contents);
		}
		
		if ($buffering) $this->start();
		return $key;
	}
	/**
	 * 
	 * @return array
	 */
	public function getBreakPointNames() {
		return array_keys($this->breakPoints);
	}
	/**
	 * @return boolean
	 */
	public function hasBreakPoint($name) {
		return array_key_exists($name, $this->breakPoints);
	}
	/**
	 * 
	 * @param string $key
	 * @param string $contents
	 */
	public function insertOnBreakPoint($key, $contents) {
		if (!mb_strlen($contents) || !isset($this->breakPoints[$key])) return;

		$buffering = $this->isBuffering();
		if ($buffering) $this->end();

		$contentLength = mb_strlen($contents);
		$pos = $this->breakPoints[$key];
		
		$this->contents = StringUtils::insert($this->contents, $pos, $contents);

		foreach ($this->breakPoints as $breakKey => $breakPos) {
			if ($breakPos >= $pos) {
				$this->breakPoints[$breakKey] = $breakPos + $contentLength;
			}
		}

		if ($buffering) $this->start();
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBufferedContents(): string {
		if ($this->isBuffering()) {
			return $this->contents . ob_get_contents();
		}
		return $this->contents;
	}
	/**
	 * 
	 * @return string
	 */
	public function get() {
		return $this->contents;
	}
	/**
	 * 
	 * @return boolean
	 */
	public static function isOnline() {
		return (bool) ob_get_level();
	}
	/**
	 * @return int
	 */
	public function getLevel() {
		return $this->obLevel;
	}
}



// /**
//  * @link http://stackoverflow.com/questions/5446647/how-can-i-use-var-dump-output-buffering-without-memory-errors/
// */

// class OutputBuffer
// {
// 	/**
// 	 * @var int
// 	 */
// 	private $chunkSize;

// 	/**
// 	 * @var bool
// 	 */
// 	private $started;

// 	/**
// 	 * @var SplFileObject
// 	 */
// 	private $store;

// 	/**
// 	 * @var bool Set Verbosity to true to output analysis data to stderr
// 	 */
// 	private $verbose = true;

// 	public function __construct($chunkSize = 1024) {
// 		$this->chunkSize = $chunkSize;
// 		$this->store	 = new SplTempFileObject();
// 	}

// 	public function start() {
// 		if ($this->started) {
// 			throw new BadMethodCallException('Buffering already started, can not start again.');
// 		}
// 		$this->started = true;
// 		$result = ob_start(array($this, 'bufferCallback'), $this->chunkSize);
// 		$this->verbose && file_put_contents('php://stderr', sprintf("Starting Buffering: %d; Level %d\n", $result, ob_get_level()));
// 		return $result;
// 	}

// 	public function flush() {
// 		$this->started && ob_flush();
// 	}

// 	public function stop() {
// 		if ($this->started) {
// 			ob_flush();
// 			$result = ob_end_flush();
// 			$this->started = false;
// 			$this->verbose && file_put_contents('php://stderr', sprintf("Buffering stopped: %d; Level %d\n", $result, ob_get_level()));
// 		}
// 	}

// 	private function bufferCallback($chunk, $flags) {

// 		$chunkSize = strlen($chunk);

// 		if ($this->verbose) {
// 			$level	 = ob_get_level();
// 			$constants = ['PHP_OUTPUT_HANDLER_START', 'PHP_OUTPUT_HANDLER_WRITE', 'PHP_OUTPUT_HANDLER_FLUSH', 'PHP_OUTPUT_HANDLER_CLEAN', 'PHP_OUTPUT_HANDLER_FINAL'];
// 			$flagsText = '';
// 			foreach ($constants as $i => $constant) {
// 				if ($flags & ($value = constant($constant)) || $value == $flags) {
// 					$flagsText .= (strlen($flagsText) ? ' | ' : '') . $constant . "[$value]";
// 				}
// 			}

// 			file_put_contents('php://stderr', "Buffer Callback: Chunk Size $chunkSize; Flags $flags ($flagsText); Level $level\n");
// 		}

// 		if ($flags & PHP_OUTPUT_HANDLER_FINAL) {
// 			return TRUE;
// 		}

// 		if ($flags & PHP_OUTPUT_HANDLER_START) {
// 			$this->store->fseek(0, SEEK_END);
// 		}

// 		$chunkSize && $this->store->fwrite($chunk);

// 		if ($flags & PHP_OUTPUT_HANDLER_FLUSH) {
// 			// there is nothing to d
// 		}

// 		if ($flags & PHP_OUTPUT_HANDLER_CLEAN) {
// 			$this->store->ftruncate(0);
// 		}

// 		return "";
// 	}

// 	public function getSize() {
// 		$this->store->fseek(0, SEEK_END);
// 		return $this->store->ftell();
// 	}

// 	public function getBufferFile() {
// 		return $this->store;
// 	}

// 	public function getBuffer() {
// 		$array = iterator_to_array($this->store);
// 		return implode('', $array);
// 	}

// 	public function __toString(): string {
// 		return $this->getBuffer();
// 	}

// 	public function endClean() {
// 		return ob_end_clean();
// 	}
// }


// $buffer  = new OutputBuffer();
// echo "Starting Buffering now.\n=======================\n";
// $buffer->start();

// foreach (range(1, 10) as $iteration) {
// 	$string = "fill{$iteration}";
// 	echo str_repeat($string, 100), "\n";
// }
// $buffer->stop();

// echo "Buffering Results:\n==================\n";
// $size = $buffer->getSize();
// echo "Buffer Size: $size (string length: ", strlen($buffer), ").\n";
// echo "Peeking into buffer: ", var_dump(substr($buffer, 0, 10)), ' ...', var_dump(substr($buffer, -10)), "\n";
