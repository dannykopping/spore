<?php
	namespace Spore\ReST\Model;

	class Response
	{
		public $status;

		public $headers;

		/**
		 * @var \Slim\Http\Response
		 */
		private $response;

		/**
		 * Slim Response object
		 *
		 * @return \Slim\Http\Response
		 */
		public function response()
		{
			return $this->response;
		}

		public function setResponse($response)
		{
			$this->response = $response;
		}
	}
