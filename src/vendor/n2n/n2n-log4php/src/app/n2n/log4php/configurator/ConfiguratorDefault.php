<?php
namespace n2n\log4php\configurator;
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
 * Default implementation of the logger configurator.
 * 
 * Configures log4php based on a provided configuration file or array.
 * 
 * @package log4php
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version $Revision: 1394956 $
 * @since 2.2
 */
class ConfiguratorDefault implements \n2n\log4php\LoggerConfigurator 
{
	/** XML configuration file format. */
	const FORMAT_XML = 'xml';
	
	/** PHP configuration file format. */
	const FORMAT_PHP = 'php';
	
	/** INI (properties) configuration file format. */
	const FORMAT_INI = 'ini';

	/** Defines which adapter should be used for parsing which format. */
	private $adapters = array(
		self::FORMAT_XML => '\n2n\log4php\configuration\adapter\AdapterXML',
		self::FORMAT_INI => '\n2n\log4php\configuration\adapter\AdapterINI',
		self::FORMAT_PHP => '\n2n\log4php\configuration\adapter\AdapterPHP',
	);
	
	/** Default configuration; used if no configuration file is provided. */
	private static $defaultConfiguration = array(
        'threshold' => 'ALL',
        'rootLogger' => array(
            'level' => 'DEBUG',
            'appenders' => array('default'),
        ),
        'appenders' => array(
            'default' => array(
                'class' => 'n2n\log4php\appender\AppenderEcho'
            ),
        ),
	);
	
	/** Holds the appenders before they are linked to loggers. */
	private $appenders = array();
	
	/**
	 * Configures log4php based on the given configuration. The input can 
	 * either be a path to the config file, or a PHP array holding the 
	 * configuration. 
	 * 
	 * If no configuration is given, or if the given configuration cannot be
	 * parsed for whatever reason, a warning will be issued, and log4php
	 * will use the default configuration contained in 
	 * {@link $defaultConfiguration}.
	 * 
	 * @param \n2n\log4php\LoggerHierarchy $hierarchy The hierarchy on which to perform 
	 * 		the configuration. 
	 * @param string|array $input Either path to the config file or the 
	 * 		configuration as an array. If not set, default configuration 
	 * 		will be used.
	 */
	public function configure(\n2n\log4php\LoggerHierarchy $hierarchy, $input = null) {
		$config = $this->parse($input);
		$this->doConfigure($hierarchy, $config);
	}
	
	/**
	 * Parses the given configuration and returns the parsed configuration
	 * as a PHP array. Does not perform any configuration. 
	 * 
	 * If no configuration is given, or if the given configuration cannot be
	 * parsed for whatever reason, a warning will be issued, and the default 
	 * configuration will be returned ({@link $defaultConfiguration}).
	 * 
	 * @param string|array $input Either path to the config file or the 
	 * 		configuration as an array. If not set, default configuration 
	 * 		will be used.
	 * @return array The parsed configuration.
	 */
	public function parse($input) {
		// No input - use default configuration
		if (!isset($input)) {
			$config = self::$defaultConfiguration;
		}
		
		// Array input - contains configuration within the array
		else if (is_array($input)) {
			$config = $input;
		}
		
		// String input - contains path to configuration file
		else if (is_string($input)) {
			try {
				$config = $this->parseFile($input);
			} catch (\n2n\log4php\LoggerException $e) {
				$this->warn("Configuration failed. " . $e->getMessage() . " Using default configuration.");
				$config = self::$defaultConfiguration;
			}
		}
		
		// Anything else is an error
		else {
			$this->warn("Invalid configuration param given. Reverting to default configuration.");
			$config = self::$defaultConfiguration;
		}
		
		return $config;
	}

	/** 
	 * Returns the default log4php configuration.
	 * @return array
	 */
	public static function getDefaultConfiguration() {
		return self::$defaultConfiguration;
	} 
	
	/**
	 * Loads the configuration file from the given URL, determines which
	 * adapter to use, converts the configuration to a PHP array and
	 * returns it.
	 *
	 * @param string $url Path to the config file.
	 * @return The configuration from the config file, as a PHP array.
	 * @throws \n2n\log4php\LoggerException If the configuration file cannot be loaded, or
	 * 		if the parsing fails.
	 */
	private function parseFile($url) {
		
		if (!file_exists($url)) {
			throw new \n2n\log4php\LoggerException("File not found at [$url].");
		}
		
		$type = $this->getConfigType($url);
		$adapterClass = $this->adapters[$type];

		$adapter = new $adapterClass();
		return $adapter->convert($url);
	}
	
	/** Determines configuration file type based on the file extension. */
	private function getConfigType($url) {
		$info = pathinfo($url);
		$ext = strtolower($info['extension']);
		
		switch($ext) {
			case 'xml':
				return self::FORMAT_XML;
			
			case 'ini':
			case 'properties':
				return self::FORMAT_INI;
			
			case 'php':
				return self::FORMAT_PHP;
				
			default:
				throw new \n2n\log4php\LoggerException("Unsupported configuration file extension: $ext");
		}
	}
	
	/**
	 * Constructs the logger hierarchy based on configuration.
	 * 
	 * @param \n2n\log4php\LoggerHierarchy $hierarchy
	 * @param array $config
	 */
	private function doConfigure(\n2n\log4php\LoggerHierarchy $hierarchy, $config) {
		if (isset($config['threshold'])) {
			$threshold = \n2n\log4php\LoggerLevel::toLevel($config['threshold']);
			if (isset($threshold)) {
				$hierarchy->setThreshold($threshold);
			} else {
				$this->warn("Invalid threshold value [{$config['threshold']}] specified. Ignoring threshold definition.");
			}
		}
		
		// Configure appenders and add them to the appender pool
		if (isset($config['appenders']) && is_array($config['appenders'])) {
			foreach($config['appenders'] as $name => $appenderConfig) {
				$this->configureAppender($name, $appenderConfig);
			}
		}
		
		// Configure root logger 
		if (isset($config['rootLogger'])) {
			$this->configureRootLogger($hierarchy, $config['rootLogger']);
		}
		
		// Configure loggers
		if (isset($config['loggers']) && is_array($config['loggers'])) {
			foreach($config['loggers'] as $loggerName => $loggerConfig) {
				$this->configureOtherLogger($hierarchy, $loggerName, $loggerConfig);
			}
		}

		// Configure renderers
		if (isset($config['renderers']) && is_array($config['renderers'])) {
			foreach($config['renderers'] as $rendererConfig) {
				$this->configureRenderer($hierarchy, $rendererConfig);
			}
		}
		
		if (isset($config['defaultRenderer'])) {
			$this->configureDefaultRenderer($hierarchy, $config['defaultRenderer']);
		}
	}
	
	private function configureRenderer(\n2n\log4php\LoggerHierarchy $hierarchy, $config) {
		if (empty($config['renderingClass'])) {
			$this->warn("Rendering class not specified. Skipping renderer definition.");
			return;
		}
		
		if (empty($config['renderedClass'])) {
			$this->warn("Rendered class not specified. Skipping renderer definition.");
			return;
		}
		
		// Error handling performed by RendererMap
		$hierarchy->getRendererMap()->addRenderer($config['renderedClass'], $config['renderingClass']);
	}
	
	private function configureDefaultRenderer(\n2n\log4php\LoggerHierarchy $hierarchy, $class) {
		if (empty($class)) {
			$this->warn("Rendering class not specified. Skipping default renderer definition.");
			return;
		}
		
		// Error handling performed by RendererMap
		$hierarchy->getRendererMap()->setDefaultRenderer($class);
	}
	
	/** 
	 * Configures an appender based on given config and saves it to 
	 * {@link $appenders} array so it can be later linked to loggers. 
	 * @param string $name Appender name. 
	 * @param array $config Appender configuration options.
	 */
	private function configureAppender($name, $config) {

		// TODO: add this check to other places where it might be useful
		if (!is_array($config)) {
			$type = gettype($config);
			$this->warn("Invalid configuration provided for appender [$name]. Expected an array, found <$type>. Skipping appender definition.");
			return;
		}
		
		// Parse appender class
		$class = $config['class'];
		if (empty($class)) {
			$this->warn("No class given for appender [$name]. Skipping appender definition.");
			return;
		}
		if (!class_exists($class)) {
			$this->warn("Invalid class [$class] given for appender [$name]. Class does not exist. Skipping appender definition.");
			return;
		}
		
		// Instantiate the appender
		$appender = new $class($name);
		if (!($appender instanceof \n2n\log4php\LoggerAppender)) {
			$this->warn("Invalid class [$class] given for appender [$name]. Not a valid \n2n\log4php\LoggerAppender class. Skipping appender definition.");
			return;
		}
		
		// Parse the appender threshold
		if (isset($config['threshold'])) {
			$threshold = \n2n\log4php\LoggerLevel::toLevel($config['threshold']);
			if ($threshold instanceof \n2n\log4php\LoggerLevel) {
				$appender->setThreshold($threshold);
			} else {
				$this->warn("Invalid threshold value [{$config['threshold']}] specified for appender [$name]. Ignoring threshold definition.");
			}
		}
		
		// Parse the appender layout
		if ($appender->requiresLayout() && isset($config['layout'])) {
			$this->createAppenderLayout($appender, $config['layout']);
		}
		
		// Parse filters
		if (isset($config['filters']) && is_array($config['filters'])) {
			foreach($config['filters'] as $filterConfig) {
				$this->createAppenderFilter($appender, $filterConfig);
			}
		}
		
		// Set options if any
		if (isset($config['params'])) {
			$this->setOptions($appender, $config['params']);
		}

		// Activate and save for later linking to loggers
		$appender->activateOptions();
		$this->appenders[$name] = $appender;
	}
	
	/**
	 * Parses layout config, creates the layout and links it to the appender.
	 * @param \n2n\log4php\LoggerAppender $appender
	 * @param array $config Layout configuration.
	 */
	private function createAppenderLayout(\n2n\log4php\LoggerAppender $appender, $config) {
		$name = $appender->getName();
		$class = $config['class'];
		if (empty($class)) {
			$this->warn("Layout class not specified for appender [$name]. Reverting to default layout.");
			return;
		}
		if (!class_exists($class)) {
			$this->warn("Nonexistant layout class [$class] specified for appender [$name]. Reverting to default layout.");
			return;
		}
		
		$layout = new $class();
		if (!($layout instanceof \n2n\log4php\LoggerLayout)) {
			$this->warn("Invalid layout class [$class] sepcified for appender [$name]. Reverting to default layout.");
			return;
		}
		
		if (isset($config['params'])) {
			$this->setOptions($layout, $config['params']);
		}
		
		$layout->activateOptions();
		$appender->setLayout($layout);
	}
	
	/**
	 * Parses filter config, creates the filter and adds it to the appender's 
	 * filter chain.
	 * @param \n2n\log4php\LoggerAppender $appender
	 * @param array $config Filter configuration.
	 */
	private function createAppenderFilter(\n2n\log4php\LoggerAppender $appender, $config) {
		$name = $appender->getName();
		$class = $config['class'];
		if (!class_exists($class)) {
			$this->warn("Nonexistant filter class [$class] specified on appender [$name]. Skipping filter definition.");
			return;
		}
	
		$filter = new $class();
		if (!($filter instanceof \n2n\log4php\LoggerFilter)) {
			$this->warn("Invalid filter class [$class] sepcified on appender [$name]. Skipping filter definition.");
			return;
		}
	
		if (isset($config['params'])) {
			$this->setOptions($filter, $config['params']);
		}
	
		$filter->activateOptions();
		$appender->addFilter($filter);
	}
	
	/** 
	 * Configures the root logger
	 * @see configureLogger() 
	 */
	private function configureRootLogger(\n2n\log4php\LoggerHierarchy $hierarchy, $config) {
		$logger = $hierarchy->getRootLogger();
		$this->configureLogger($logger, $config);
	}

	/**
	 * Configures a logger which is not root.
	 * @see configureLogger()
	 */
	private function configureOtherLogger(\n2n\log4php\LoggerHierarchy $hierarchy, $name, $config) {
		// Get logger from hierarchy (this creates it if it doesn't already exist)
		$logger = $hierarchy->getLogger($name);
		$this->configureLogger($logger, $config);
	}
	
	/**
	 * Configures a logger. 
	 * 
	 * @param \n2n\log4php\Logger $logger The logger to configure
	 * @param array $config \n2n\log4php\Logger configuration options.
	 */
	private function configureLogger(\n2n\log4php\Logger $logger, $config) {
		$loggerName = $logger->getName();
		
		// Set logger level
		if (isset($config['level'])) {
			$level = \n2n\log4php\LoggerLevel::toLevel($config['level']);
			if (isset($level)) {
				$logger->setLevel($level);
			} else {
				$this->warn("Invalid level value [{$config['level']}] specified for logger [$loggerName]. Ignoring level definition.");
			}
		}
		
		// Link appenders to logger
		if (isset($config['appenders'])) {
			foreach($config['appenders'] as $appenderName) {
				if (isset($this->appenders[$appenderName])) {
					$logger->addAppender($this->appenders[$appenderName]);
				} else {
					$this->warn("Nonexistnant appender [$appenderName] linked to logger [$loggerName].");
				}
			}
		}
		
		// Set logger additivity
		if (isset($config['additivity'])) {
			try {
				$additivity = \n2n\log4php\option\OptionConverter::toBooleanEx($config['additivity'], null);
				$logger->setAdditivity($additivity);
			} catch (Exception $ex) {
				$this->warn("Invalid additivity value [{$config['additivity']}] specified for logger [$loggerName]. Ignoring additivity setting.");
			}
		}
	}

	/**
	 * Helper method which applies given options to an object which has setters
	 * for these options (such as appenders, layouts, etc.).
	 * 
	 * For example, if options are:
	 * <code>
	 * array(
	 * 	'file' => '/tmp/myfile.log',
	 * 	'append' => true
	 * )
	 * </code>
	 * 
	 * This method will call:
	 * <code>
	 * $object->setFile('/tmp/myfile.log')
	 * $object->setAppend(true)
	 * </code>
	 * 
	 * If required setters do not exist, it will produce a warning. 
	 * 
	 * @param mixed $object The object to configure.
	 * @param unknown_type $options
	 */
	private function setOptions($object, $options) {
		foreach($options as $name => $value) {
			$setter = "set$name";
			if (method_exists($object, $setter)) {
				$object->$setter($value);
			} else {
				$class = get_class($object);
				$this->warn("Nonexistant option [$name] specified on [$class]. Skipping.");
			}
		}
	}
	
	/** Helper method to simplify error reporting. */
	private function warn($message) {
		throw new \n2n\log4php\LoggerException("log4php: $message", E_USER_WARNING);
	}
}
