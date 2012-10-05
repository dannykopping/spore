<?php
	namespace Spore\ReST\Data\Middleware;

	use Slim\Middleware;

	/**
	 *
	 */
	class Response extends Middleware
	{

		/**
		 * Call
		 *
		 * Perform actions specific to this middleware and optionally
		 * call the next downstream middleware.
		 */
		public function call()
		{
			// Get reference to application
			$app = $this->app;

			// Run inner middleware and application
			$this->next->call();
		}
	}
