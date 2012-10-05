<?php
	namespace Spore\ReST;

	use Slim\Slim;
	use Spore\ReST\Data\Deserializer;
	use ReflectionClass;
	use Spore\ReST\AutoRoute\AutoRouter;
	use Spore\ReST\Data\Middleware\DeserializerMiddleware;
	use Spore\ReST\Model\Response;
	use Spore\ReST\Model\Request;
	use Slim\Route;
	use Spore\ReST\Data\Serializer;
	use Spore\Auth\AccessController;
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;
	use Spore\Config\Configuration;
	use Exception;

	/**
	 * Class to control the operation and configuration of Slim Framework
	 */
	class Controller
	{
		private static $instance;

		/**
		 * @var Slim
		 */
		private $_slimInstance;

		private $_authorizationCallback;

		/**
		 * Initialize the Controller
		 *
		 * @throws Exception
		 */
		private function initialize()
		{
			$app = $this->getApp();

			if(empty($app))
				throw new Exception("Controller could not be initialized with an empty Slim instance");

			$app->config('debug', Configuration::get("debug"));

			// add Slim middleware to deserialize HTTP request body data
			$this->addRequestBodyDeserializer();
		}

		/**
		 * @static
		 * @return Controller
		 */
		public static function getInstance()
		{
			if(empty(self::$instance))
			{
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		public function setApp(Slim $slimInstance)
		{
			$this->_slimInstance = $slimInstance;

			$this->initialize();
		}

		public function getApp()
		{
			return $this->_slimInstance;
		}

		private function addRequestBodyDeserializer()
		{
			$app = $this->getApp();

			$deserializer = new Deserializer($app);
			$app->add($deserializer);
		}

		/**
		 * Recursively scans the PHP_SERVICES directory, requiring each PHP class it finds, and returning an
		 * array of classes to be added to the auto-route list
		 *
		 * @return array
		 */
		public function getAllPHPServices()
		{
			$servicesDir 	= Configuration::get("services");
			$servicesNS 	= Configuration::get("services-ns");
			$files          = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($servicesDir), RecursiveIteratorIterator::LEAVES_ONLY);
			$classes        = array();

			foreach($files as $file)
			{
				if(empty($file))
					continue;

				$e = explode('.', $file->getFileName());
				if(empty($e) || count($e) < 2)
					continue;

				$path      = $file->getRealPath();
				$className = $e[0];
				$extension = $e[1];

				if($extension != "php")
					continue;

				if(!empty($servicesNS))
					$className = $servicesNS . "\\$className";

				require_once $path;
				$classes[] = new $className;
			}

			return $classes;
		}

		public function addAutoRouting(array $classes)
		{
			$app = $this->getApp();
			$router = new AutoRouter($app, $classes);
		}

		public function setAuthCallback($authorizationCallback)
		{
			$app = $this->getApp();
			$access = new AccessController($app);
			$access->authorizationCallback($authorizationCallback);

			if(!is_callable($authorizationCallback))
			{
				$this->_authorizationCallback = null;
				AccessController::authorizationCallback(null);

				throw new Exception("Function used for setAuthCallback is not callable.");
			}

			$this->_authorizationCallback = $authorizationCallback;

			AccessController::authorizationCallback($this->getAuthCallback());
		}

		public function getAuthCallback()
		{
			return $this->_authorizationCallback;
		}
	}