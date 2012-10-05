<?php
	namespace Spore;

	use Slim\Slim;
	use Spore\ReST\AutoRoute\Router;

	/**
	 *
	 */
	class Spore extends Slim
	{
		public function __construct($userSettings = array())
		{
			parent::__construct($userSettings);

        	$this->router = new Router($this);
		}


		/********************************************************************************
		 * PSR-0 Autoloader
		 *
		 * Do not use if you are using Composer to autoload dependencies.
		 *******************************************************************************/

		/**
		 * Slim PSR-0 autoloader from Slim Framework
		 */
		public static function autoload($className)
		{
			$thisClass = str_replace(__NAMESPACE__ . '\\', '', __CLASS__);

			$baseDir = __DIR__;

			if(substr($baseDir, -strlen($thisClass)) === $thisClass)
			{
				$baseDir = substr($baseDir, 0, -strlen($thisClass));
			}

			$className = ltrim($className, '\\');
			$fileName  = $baseDir;
			$namespace = '';
			if($lastNsPos = strripos($className, '\\'))
			{
				$namespace = substr($className, 0, $lastNsPos);
				$namespace = substr($namespace, (strpos($namespace, __NAMESPACE__) + strlen(__NAMESPACE__)));
				$className = substr($className, $lastNsPos + 1);
				$fileName .= __NAMESPACE__ . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$fileName .= DIRECTORY_SEPARATOR . $className . '.php';

			if(file_exists($fileName))
			{
				require_once $fileName;
			}
		}

		/**
		 * Register the PSR-0 autoloader
		 */
		public static function registerAutoloader()
		{
			spl_autoload_register(__NAMESPACE__ . "\\Spore::autoload");
		}

		public function run()
		{
			parent::run();
		}
	}
