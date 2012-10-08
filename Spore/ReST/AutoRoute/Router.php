<?php
	namespace Spore\ReST\AutoRoute;

	use Slim\Slim;
	use Spore\Spore;
	use Spore\ReST\Model\Response;
	use Spore\ReST\Data\Serializer;
	use Spore\ReST\Model\Request;

	/**
	 *
	 */
	class Router extends \Slim\Router
	{
		private $app;

		public function __construct(Slim $app)
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

		public function dispatch(\Slim\Route $route)
		{
			$app      = $this->getApp();
			$params   = $route->getParams();
			$callable = $route->getCallable();

			// check for a matching autoroute based on the request URI
			$autoroute = null;
			foreach($app->routes as $r)
			{
				$matches = $route->matches($r->getUri());
				if($matches)
					$autoroute = $r;
			}

			// build Request and Response objects to be passed to callable
			$req  = $this->getRequestData($route, $params);
			$resp = $this->getResponseData();

			if(!is_callable($callable))
				return false;

			// call the autoroute's callback function and pass in the Request and Response objects
			$result      = call_user_func_array($callable, array($req, &$resp));
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

			$app->status($resp->status);
			$app->response()->body($output);

			return true;
		}

		/**
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

			if(!empty($params))
				$req->params = $params;

			if((in_array("PUT", $route->getHttpMethods()) || in_array("POST", $route->getHttpMethods())) && !empty($body))
				$req->data = $body;

			if(!empty($env["QUERY_STRING"]))
			{
				parse_str($env["QUERY_STRING"], $query);
				$req->queryParams = $query;
			}

			$req->setRequest($request);

			return $req;
		}

		/**
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
					break;
				default:
					if(!$app->request()->isAjax())
					{
						$app->render($template, $data);
						$output = ob_get_clean();
					}
					break;
			}

			return $output;
		}
	}
