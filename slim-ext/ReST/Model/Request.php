<?php
	namespace ReST\Model;

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


		public function __get($name)
		{
			return $this->$name;
		}

		public function __set($name, $value)
		{
			$this->$name = $value;
		}
	}
