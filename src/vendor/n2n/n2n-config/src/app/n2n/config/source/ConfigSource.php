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
namespace n2n\config\source;

interface ConfigSource {
	/**
	 * @return array
	 * @throws CorruptedConfigSourceException
	 */
	public function readArray(): array;
	
	/**
	 * Returns a string which identifies a given config source. If the config source gets changed 
	 * it should be different too. The hash code is used to determine if a cached config source
	 * is still valid. Generating the hash code should be fast. If your config source is an xml
	 * file for example you are not supposed to read the whole file and and generete md5-hash over 
	 * it. A good hash code whould include the timestamp when the file was last modified. If this 
	 * is not possible just return null. If this method returns null this config source can't
	 * be cached. 
	 * @return string the hash code, null if no hash code can be generated.
	 */
	public function hashCode();
	
	/**
	 * Returns a string which identifies the datasource. This method is used to build error messages.
	 * @return string
	 */
	public function __toString();
}
