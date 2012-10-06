<?php
	namespace Spore\ReST\Model;

	class Request
	{
		/**
		 * @var stdClass|array		Stores the deserialized request body
		 */
		public $data = null;

		/**
		 * @var array				(REST only) HTTP GET parameters
		 */
		public $queryParams = array();

		/**
		 * @var array				(REST only) Route parameters
		 */
		public $params = array();

		/**
		 * @var \Slim\Http\Request
		 */
		private $request;

		/**
		 * Slim Response object
		 *
		 * @return \Slim\Http\Request
		 */
		public function request()
		{
			return $this->request;
		}

		public function setRequest($request)
		{
			$this->request = $request;
		}
	}
