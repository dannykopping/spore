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
		 * @var ReST\Controller			The Spore application controller singleton instance
		 */
		private $controller;

		/**
		 * @var array					An array of auto-routes
		 */
		private $autoroutes;

		/**
		 * @var	callable				The authorization failed callback function
		 */
		private $authFailedHandler;

		/**
		 * Constructor
		 *
		 * @param array $userSettings
		 */
		public function __construct($userSettings = array())
		{
			$this->autoroutes = array();

			parent::__construct($userSettings);
			$this->init();
		}

		/**
		 * Combine the default Slim configuration with
		 * the default Spore configuration
		 *
		 * @return array
		 */
		static function getDefaultSettings()
		{
			$default = parent::getDefaultSettings();

			$extended = array(
				"debug" => "true",
				"content-type" => "application/json",
				"gzip" => true,
				"services" => realpath(dirname(__DIR__)."/examples/services"),
				"templates.path" => realpath(dirname(__DIR__)."/examples/templates"),
				"services-ns" => "Spore\\Services",

				"deserializers" => array(
					"application/json" 						=> "\\Spore\\ReST\\Data\\Deserializer\\JSONDeserializer",
					"application/xml"  						=> "\\Spore\\ReST\\Data\\Deserializer\\XMLDeserializer",
					"text/xml"         						=> "\\Spore\\ReST\\Data\\Deserializer\\XMLDeserializer",
					"text/csv"         						=> "\\Spore\\ReST\\Data\\Deserializer\\CSVDeserializer",
					"application/x-www-form-urlencoded"     => "\\Spore\\ReST\\Data\\Deserializer\\FormDeserializer",
					"multipart/form-data"      				=> "\\Spore\\ReST\\Data\\Deserializer\\FormDeserializer"
				),

				"serializers" => array(
					"application/json" 						=> "\\Spore\\ReST\\Data\\Serializer\\JSONSerializer",
					"application/xml" 						=> "\\Spore\\ReST\\Data\\Serializer\\XMLSerializer",
					"text/xml"         						=> "\\Spore\\ReST\\Data\\Serializer\\XMLSerializer",
				)
			);

			return array_merge($default, $extended);
		}

		/**
		 *	Initialize the Spore application
		 * 	and override a few Slim internals
		 */
		private function init()
		{
			$this->controller = Controller::getInstance();
			$this->router     = new Router($this); // override router class

			$this->controller->setApp($this);

			$this->error(array($this, "errorHandler")); // add default error handler
			$this->notFound(array($this, "notFoundHandler")); // add default not found handler
			$this->authFailed(array($this, "authFailedHandler")); // add default authorization failed handler
			$this->authCallback(array($this, "defaultAuthCallback")); // add default auth callback

			$this->updateAutoRoutes();
		}

		/**
		 *	Update the controller's auto-route classes
		 */
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

		/**
		 * Set the authorization callback function
		 *
		 * @param $authorizationCallback
		 *
		 * @return mixed
		 */
		public function authCallback($authorizationCallback = null)
		{
			if(is_callable($authorizationCallback)) {
				$this->controller->setAuthCallback($authorizationCallback);
				return;
			}

			return $this->controller->getAuthCallback();
		}

		/**
		 * Define or get the authorization failed handler
		 *
		 * @param null $argument
		 */
		public function authFailed($argument = null)
		{
			if (is_callable($argument)) {
				//Register error handler
				$this->authFailedHandler = $argument;
			} else {
				//Invoke error handler
				$this->response->status(Status::UNAUTHORIZED);
				$this->response->body('');
				$this->response->write($this->callAuthFailedHandler($argument));
				$this->stop();
			}
		}

		/**
		 * @param $argument
		 *
		 * @return string
		 */
		private function callAuthFailedHandler($argument)
		{
			ob_start();
			if ( is_callable($this->authFailedHandler) ) {
				call_user_func_array($this->authFailedHandler, array($argument));
			} else {
				call_user_func_array(array($this, 'authFailedHandler'), array($argument));
			}

			return ob_get_clean();
		}

		/**
		 * Get the default authorization callback function
		 *
		 * @return bool
		 */
		public function defaultAuthCallback()
		{
			return true;
		}

		/**
		 * Get the default error callback function
		 *
		 * @param \Exception $e
		 */
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

		/**
		 *	Get the not found callback function
		 */
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

		/**
		 *	Get the default authorization failed callback function
		 */
		public function authFailedHandler()
		{
			$this->contentType($this->config("content-type"));
			$data = Serializer::getSerializedData($this, array(
															  "message" => "You are not authorized to execute this function"
														 ));

			$this->halt(Status::UNAUTHORIZED, $data);
		}
	}
