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

			// set debug mode to the Aerial DEBUG_MODE setting
			$app->config('debug', Configuration::get("debug"));

			// add Slim middleware to deserialize HTTP request body data
			$this->addRequestBodyDeserializer();

			// define a custom router function
//			$app->customRouter(array($this, "router"));

			// define a custom error-handling function
			$app->error(array($this, "errorHandler"));

			// define a 404 handling function
			$app->notFound(array($this, "notFoundHandler"));

			// add default Aerial operations
			$this->addDefaultOperations();

			// add internal test service for auto-routing
			$this->addAutoRouting(array(new InternalTestService()));
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

		public function errorHandler(Exception $e)
		{
			$app = $this->getApp();

			$app->contentType("application/json");

			$app->halt(500, json_encode(array(
											 "error" => array(
												 "message" => $e->getMessage(),
												 "code"    => $e->getCode(),
												 "file"    => $e->getFile(),
												 "line"    => $e->getLine(),
											 )
										)));
		}

		public function notFoundHandler()
		{
			$app = $this->getApp();

			$app->contentType("application/json");
			$app->response()->header("Access-Control-Allow-Origin", "*");

			$app->halt(500, json_encode(array(
											 "error" => array(
												 "message" => "'" . $app->request()->getResourceUri() . "' could not be resolved to a valid API call",
												 "code"    => 500
											 )
										)));
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
//			$app->registerPlugin("AutoRouter", $classes);
		}

		public function setAuthCallback($authorizationCallback)
		{
			$app = $this->getApp();
			$access = new AccessController($app);
			$access->authorizationCallback($authorizationCallback);

//			$app->registerPlugin("AccessController");

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

		private function addDefaultOperations()
		{
			$app = $this->getApp();

			$app->get("/", function ()
			{
				return array("message" => "Hello World from Aerial Framework");
			});

			if(Configuration::get("debug"))
			{
				// TODO: Add more status info here
				$app->get("/status", function ()
				{
					return array("status" => "operational");
				});
			}
		}
	}

	class InternalTestService
	{
		/**
		 * @url             	/internal/test/simple
		 * @methods         	GET
		 */
		public function simple()
		{
			return array("message" => "Hello World");
		}
	}