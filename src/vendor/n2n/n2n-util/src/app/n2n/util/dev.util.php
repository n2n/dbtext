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
function test(...$values) {
	if (n2n\core\N2N::isLiveStageOn()) return;
	$tab = '    ';
	$buildOutputClosure = function($buildOutputClosure, string $prepend, $value) use ($tab) {
		$output = '';
		$testArrayFunc = function(string $prepend, $name, $values) use ($buildOutputClosure, $tab) {
			$output = $prepend . $name ;
			if (is_array($values) || (is_object($values) && is_a($values, 'Countable'))) {
				$output .= '(' . count($values) . ')';
			}
				
			$output .= ' {' . "\r\n";
			foreach ($values as $key => $value) {
				$output .= $prepend .  $tab . '[' . $key . ']=>' . "\r\n";
				$output .= $buildOutputClosure($buildOutputClosure, $prepend . $tab, $value);
			}
				
			$output .= $prepend . '}';
				
			return $output;
		};

		if (is_object($value)) {
			if (is_a($value, 'Traversable')) {
				$output .= $testArrayFunc($prepend, 'object(' . get_class($value) . ')',  $value);
			} else {
				$output .= $prepend . 'object(' . get_class($value) . ')';
				if (method_exists($value, '__toString')) {
					$output .= ": " . $value;
				} else if (method_exists($value, 'getId') && is_callable(array($value, 'getId'))) {
					$id = $value->getId();
					if (is_scalar($id)) {
						$output .= ": #" . $id;
					}
				}
				$output .= "\r\n";
			}
		} else {
			if (is_array($value)) {
				$output .= $testArrayFunc($prepend, 'array', $value);
				$output .= "\r\n";
			} else {
				$output .= $prepend;
				$output .= gettype($value) . '(' . mb_strlen($value) . ') "' . $value . '"';
			}
		}

		return $output;
	};

	echo '<pre>';
	foreach ($values as $value) {
		$output = 0;
		ob_start(function($content) use (&$output) {
			$output++;
			return '';
		}, 4096);
		var_dump($value);
		if ($output === 0) {
			$contents = ob_get_contents();
			ob_end_flush();
			echo $contents;
			continue;
		}
		ob_end_clean();
		echo $buildOutputClosure($buildOutputClosure, '', $value);
	}

	echo '</pre>';
}
