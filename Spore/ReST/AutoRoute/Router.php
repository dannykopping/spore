<?php
	namespace Spore\ReST\AutoRoute;

	use Slim\Slim;
	use Spore\Spore;
	use Spore\ReST\Model\Response;
	use Spore\ReST\Data\Serializer;
	use Spore\ReST\Model\Request;

	/**
	 *    This class overrides Slim's default routing capabilities
	 */
	class Router extends \Slim\Router
	{
		/**
		 * The related Spore application
		 *
		 * @var \Spore\Spore
		 */
		private $app;

		/**
		 * Constructor
		 *
		 * @param \Spore\Spore $app
		 */
		public function __construct(Spore $app)
		{
			parent::__construct();

			$this->app = $app;
		}

		/**
		 * @return \Spore\Spore
		 */
		public function getApp()
		{
			return $this->app;
		}

		/**
		 * Override Slim's default `dispatch` function
		 *
		 * @param \Slim\Route $route
		 *
		 * @return bool
		 */
		public function dispatch(\Slim\Route $route)
		{
			$app      = $this->getApp();
			$params   = $route->getParams();
			$callable = $route->getCallable();

			// check for a matching autoroute based on the request URI
			$autoroute = null;

			if(count($app->routes) > 0)
			{
				foreach($app->routes as $testRoute)
				{
					if(!empty($callable) && $callable === $testRoute->getCallback())
					{
						$autoroute = $testRoute;
						break;
					}
				}
			}

			// build Request and Response objects to be passed to callable
			$req  = $this->getRequestData($route, $params);
			$resp = $this->getResponseData();

			if(!is_callable($callable))
				return false;

			$passParams = $app->config("pass-params") == true;

			if($passParams)
			{
				// call the autoroute's callback function and pass in the Request and Response objects
				$result = call_user_func_array($callable, array($req, &$resp));
			}
			else
			{
				$app->applyHook("spore.autoroute.before", array(
                           "request" => &$req,
                           "response" => &$resp,
                           "autoroute" => &$autoroute,
				));

				$result = call_user_func_array($callable, array());
			}

			$outputEmpty = ob_get_length() <= 0;
			$output      = "";

			// if the output buffer is empty, we can return our own response
			if($outputEmpty)
			{
				// if there is no response data, return a blank response
				if($result === null && $result !== false)
					return true;

				if($autoroute && $autoroute->getTemplate()) $output = $this->getTemplateOutput($autoroute, $app, $result);
				else                                        $output = Serializer::getSerializedData($app, $result);

				if(empty($output))
					return true;
			}
			else
				$output = ob_get_clean();

			// return gzip-encoded data if gzip is enabled
			$gzipEnabled = $app->config("gzip");
			$env         = $app->environment();
			if(substr_count($env["ACCEPT_ENCODING"], "gzip") && extension_loaded("zlib") && $gzipEnabled)
			{
				$app->response()->header("Content-Encoding", "gzip");
				$app->response()->header("Vary", "Accept-Encoding");
				$output = gzencode($output, 9, FORCE_GZIP);
			}

			// set the HTTP status
			$app->status($resp->status);

			// set the response body
			$app->response()->body($output);

			return true;
		}

		/**
		 * Get a Request object containing relevant properties
		 *
		 * @param \Slim\Route $route
		 * @param             $params
		 *
		 * @return \Spore\ReST\Model\Request
		 */
		private function getRequestData(\Slim\Route $route, $params)
		{
			$req = new Request();

			$app = $this->getApp();
			$env = $app->environment();

			$data    = array();
			$body    = $app->request()->getBody();
			$request = $app->request();

			// assign Slim URI params to Request::$params property
			if(!empty($params))
				$req->params = $params;

			// assign deserialized HTTP request body to Request::$data property
			if((in_array("PUT", $route->getHttpMethods()) || in_array("POST", $route->getHttpMethods())))
			{
				// body was deserialized correctly
				if(!empty($body))
					$req->data = $body;
				else
				{
					if(!empty($env['slim.request.form_hash']) || !empty($_FILES))
					{
						$req->data = $env['slim.request.form_hash'];
						if(!empty($_FILES))
						{
							$req->files = $_FILES;
						}
					}
				}
			}

			if(!empty($env["QUERY_STRING"]))
			{
				parse_str($env["QUERY_STRING"], $query);

				// assign parsed URL query string to Request::$queryParams property
				$req->queryParams = $query;
			}

			// set the Slim Request object
			$req->setRequest($request);

			return $req;
		}

		/**
		 * Get a Response object containing relevant properties
		 *
		 * @return Request
		 */
		private function getResponseData()
		{
			$resp = new Response();

			$app      = $this->getApp();
			$response = $app->response();

			$resp->setResponse($response);
			$resp->headers = $response->headers();

			return $resp;
		}

        public function getRequestAndResponseObjects($route, $params)
        {
            return array($this->getRequestData($route, $params), $this->getResponseData());
        }

		/**
		 * If a @template annotation has been defined, this function will return the output
		 * of Slim's parsing of a template, if the correct @render annotation has been specified
		 *
		 * @param Route        $autoroute
		 * @param \Spore\Spore $app
		 * @param              $data
		 *
		 * @return string
		 */
		private function getTemplateOutput(Route $autoroute, Spore $app, $data)
		{
			$template   = $autoroute->getTemplate();
			$renderMode = $autoroute->getRender();

			$output = "";
			switch($renderMode)
			{
				case "always":
					$app->render($template, $data);
					$output = ob_get_clean();
					break;
				case "never":
					return Serializer::getSerializedData($app, $data);
					break;
				default:
					if(!$app->request()->isAjax())
					{
						$app->render($template, $data);
						$output = ob_get_clean();
					}
					else
						return Serializer::getSerializedData($app, $data);
					break;
			}

			return $output;
		}
	}
