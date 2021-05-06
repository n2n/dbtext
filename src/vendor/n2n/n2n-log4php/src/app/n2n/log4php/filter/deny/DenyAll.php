<?php
namespace n2n\log4php\filter\deny;
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *	   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package log4php
 */

/**
 * This filter drops all logging events. 
 * 
 * You can add this filter to the end of a filter chain to
 * switch from the default "accept all unless instructed otherwise"
 * filtering behaviour to a "deny all unless instructed otherwise"
 * behaviour.
 * 
 * <p>
 * An example for this filter:
 * 
 * {@example ../../examples/php/filter_denyall.php 19}
 *
 * <p>
 * The corresponding XML file:
 * 
 * {@example ../../examples/resources/filter_denyall.xml 18}
 *
 * @version $Revision: 883108 $
 * @package log4php
 * @subpackage filters
 * @since 0.3
 */
class DenyAll extends \n2n\log4php\LoggerFilter {

	/**
	 * Always returns the integer constant {@link \n2n\log4php\LoggerFilter::DENY}
	 * regardless of the {@link \n2n\log4php\logging\LoggingEvent} parameter.
	 * 
	 * @param \n2n\log4php\logging\LoggingEvent $event The {@link \n2n\log4php\logging\LoggingEvent} to filter.
	 * @return \n2n\log4php\LoggerFilter::DENY Always returns {@link \n2n\log4php\LoggerFilter::DENY}
	 */
	public function decide(\n2n\log4php\logging\LoggingEvent $event) {
		return \n2n\log4php\LoggerFilter::DENY;
	}
}
