<?php
	namespace Spore\ReST\AutoRoute;

	use Slim\Slim;
	use Spore\ReST\Model\Response;
	use Spore\ReST\Data\Serializer;
	use Spore\Config\Configuration;
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

		public function getApp()
		{
			return $this->app;
		}

		public function dispatch(\Slim\Route $route)
		{
			$app      = $this->getApp();
			$params   = $route->getParams();
			$callable = $route->getCallable();

			$req  = $this->getRequestData($route, $params);
			$resp = $this->getResponseData();

			if(!is_callable($callable))
				return false;

			$result = call_user_func_array($callable, array($req, &$resp));

			// if there is no response data, return a blank response
			if($result === null && $result !== false)
				return true;

			$req = Serializer::getSerializedData($app, $result);

			if(empty($req))
				return true;

			// return gzip-encoded data
			$gzipEnabled = Configuration::get("gzip");
			if(substr_count($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") && extension_loaded("zlib") && $gzipEnabled)
			{
				$app->response()->header("Content-Encoding", "gzip");
				$app->response()->header("Vary", "Accept-Encoding");
				$req = gzencode($req, 9, FORCE_GZIP);
			}

			echo $req;

			$app->status($resp->status);

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
	}
