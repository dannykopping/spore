<?php
	use Spore\ReST\Model\Request;
	use Spore\ReST\BaseService;
	use Spore\ReST\Model\Status;
	use Spore\ReST\Model\Response;

	/**
	 *
	 */
	class TestService extends BaseService
	{
		/**
		 * @url			/example1
		 * @verbs		GET
		 */
		public function example1()
		{
			return array("some" => "complex", "data" => "in an array",
						 	"with" => array("nesting"));
		}

		/**
		 * @url			/example2
		 * @verbs		GET
		 */
		public function example2(Request $request, Response $response)
		{
			// set a status code
			$response->status = Status::CREATED;

			return true;
		}

		/**
		 * @url			/example3/:param1/:param2
		 * @verbs		GET
		 */
		public function example3(Request $request, Response $response)
		{
			// use named request params
			return ((int) $request->params["param1"]) + ((int) $request->params["param2"]);
		}

		/**
		 * @url			/example4/:name
		 * @verbs		POST
		 */
		public function example4(Request $request, Response $response)
		{
			// named parameters as defined in URL with ":" prefix
			$params = $request->params;

			// parameters passed in as a query string, e.g. /url?greeting=hello
			$queryStrParams = $request->queryParams;

			// data passed along in body of HTTP request, and deserialized according to content-type
			$bodyParams = $request->data;

			return array($params, $queryStrParams, $bodyParams);
		}

		/**
		 * @url			/example5
		 * @verbs		GET
		 */
		public function example5(Request $request, Response $response)
		{
			// return complex objects
			return $request;
		}

		/**
		 * @url			/example6
		 * @verbs		GET
		 * @auth		restricted
		 */
		public function example6()
		{
			// add an @auth annotation to restrict access
			// see Spore::setAuthCallback

			return "you made it!";
		}

		/**
		 * @url			/example7
		 * @verbs		GET
		 */
		public function example7(Request $request, Response $response)
		{
			// manually set an HTTP header
			$response->headers["Look Ma"] = "No hands!";

			return "Look at the header!";
		}

		/**
		 * @url			/example8
		 * @verbs		GET
		 */
		public function example8(Request $request, Response $response)
		{
			// prevent all subsequent serialization by using `echo` instead of returning data
			// you can define the Content-Type header yourself in order for the HTTP request
			// to reflect the content being returned
			$response->headers["Content-Type"] = "application/json";

			echo json_encode(array("Hey!" => "See, I can do my own serialization!"));
		}

		/**
		 * @url            	/example9
		 * @verbs        	GET
		 * @template    	example.twig
		 * @render        	always
		 */
		public function example9()
		{
			return array(
				"name" 		  => "Twig",
				"description" => "the flexible, fast, and secure template engine for PHP",
				"url"         => "http://twig.sensiolabs.org/"
			);
		}

		/**
		 * @url            	/example10
		 * @verbs        	GET
		 * @template    	example.twig
		 * @render        	nonAJAX
		 */
		public function example10(Request $request)
		{
			return array(
				"name" 		  => "Twig",
				"description" => "the flexible, fast, and secure template engine for PHP",
				"url"         => "http://twig.sensiolabs.org/",
				"ajax"		  => $request->request()->isAjax()
			);
		}

		/**
		 * @url            	/example11/:identifier/:name
		 * @verbs        	GET
		 * @condition       identifier      [^xX]+
		 * @condition       name            [a-z]{3,}
		 */
		public function example11(Request $request)
		{
			return sprintf("Congrats %s! Your identifier is %s", $request->params['name'], $request->params['identifier']);
		}
	}
