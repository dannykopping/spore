<?php
	namespace Spore\Services;

	/**
	 *
	 */
	class TestService
	{
		/**
		 * @url			/hi
		 * @methods		GET
		 */
		public function hi()
		{
			return array("hi", "bob");
		}
	}
