<?php
	namespace Spore\Services;

	use Spore\ReST\Model\Request;
	use Spore\ReST\Model\Status;
	use Spore\ReST\Model\Response;

	/**
	 *
	 */
	class TestService
	{
		/**
		 * @url			/hi/:bob
		 * @verbs		GET,POST
		 */
		public function hi(Request $req, Response $res)
		{
			return (string) ($req|1);
		}
	}
