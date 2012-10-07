<?php
	namespace Spore;

	use Slim\Slim;
	use Spore\ReST\AutoRoute\Route;
	use Spore\ReST\Model\Status;
	use Spore\ReST\Data\Serializer;
	use Exception;
	use Spore\ReST\Controller;
	use Spore\ReST\AutoRoute\Router;

	/**
	 *
	 */
	class Spore extends Slim
	{
		/**
		 * @var ReST\Controller
		 */
		private $controller;

		private $autoroutes;

		private $authFailedHandler;

		public function __construct($userSettings = array())
		{
			$this->autoroutes = array();

			parent::__construct($userSettings);

			$this->settings = array_merge(self::getDefaultSettings(), $userSettings);
			$this->init();
		}

		public static function getDefaultSettings()
		{
			$default = parent::getDefaultSettings();

			$extended = array(
				"debug" => "true",
				"content-type" => "application/json",
				"gzip" => true,
				"services" => realpath(__DIR__."/Services"),
				"services-ns" => "Spore\\Services"
			);

			return array_merge($default, $extended);
		}

		private function init()
		{
			$this->controller = Controller::getInstance();
			$this->router     = new Router($this); // override router class

			$this->controller->setApp($this);

			$this->setErrorHandler(array($this, "errorHandler")); // add default error handler
			$this->setNotFoundHandler(array($this, "notFoundHandler")); // add default not found handler
			$this->setAuthFailedHandler(array($this, "authFailedHandler")); // add default authentication failed handler
			$this->controller->setAuthCallback(array($this, "defaultAuthCallback")); // add default auth callback

			$this->updateAutoRoutes();
		}

		public function updateAutoRoutes()
		{
			$classes = $this->controller->getAllPHPServices(); // add auto-routing
			$this->controller->addAutoRouting($classes);
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

		public function setAuthCallback($authorizationCallback)
		{
			$this->controller->setAuthCallback($authorizationCallback);
		}

		public function setErrorHandler($errorHandler)
		{
			$this->error($errorHandler);
		}

		public function setNotFoundHandler($notFoundHandler)
		{
			$this->notFound($notFoundHandler);
		}

		public function setAuthFailedHandler($authFailedHandler)
		{
			$this->authFailedHandler = $authFailedHandler;
		}

		public function getAuthFailedHandler()
		{
			return $this->authFailedHandler;
		}

		public function defaultAuthCallback()
		{
			return true;
		}

		public function errorHandler(Exception $e)
		{
			$this->contentType($this->config("content-type"));
			$data = Serializer::getSerializedData($this, array(
															  "error" => array(
																  "message" => $e->getMessage(),
																  "code"    => $e->getCode(),
																  "file"    => $e->getFile(),
																  "line"    => $e->getLine(),
															  )
														 ));

			$this->halt(Status::INTERNAL_SERVER_ERROR, $data);
		}

		public function notFoundHandler()
		{
			$this->contentType($this->config("content-type"));
			$data = Serializer::getSerializedData($this, array(
															  "error" => array(
																  "message" => "'" . $this->request()->getResourceUri() . "' could not be resolved to a valid API call",
																  "req"     => $this->request()->getIp()
															  )
														 ));

			$this->halt(Status::NOT_FOUND, $data);
		}

		public function authFailedHandler()
		{
			$this->contentType($this->config("content-type"));
			$data = Serializer::getSerializedData($this, array(
															  "message" => "You are not authorized to execute this function"
														 ));

			$this->halt(Status::UNAUTHORIZED, $data);
		}

		/**
		 * @param                      $uri
		 * @param ReST\AutoRoute\Route $route
		 *
		 * @return ReST\AutoRoute\Route
		 */
		public function autorouteMap($uri, Route $route=null)
		{
			if(empty($this->autoroutes))
				$this->autoroutes = array();

			if(empty($route))
				return isset($this->autoroutes[$uri]) ? $this->autoroutes[$uri] : null;

			$this->autoroutes[$uri] = $route;
		}
	}
