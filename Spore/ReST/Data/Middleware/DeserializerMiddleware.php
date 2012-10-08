<?php
	namespace Spore\ReST\Data\Middleware;

	use Slim\Slim;
	use Slim\Middleware;

	/**
	 *	This class is a base class which intercepts incoming requests and
	 * 	deserializes the incoming HTTP body from a predefined format into a native PHP primitive
	 */
	class DeserializerMiddleware extends Middleware
    {
        /**
         * @var Slim			The related Slim application
         */
        protected $app;

        /**
         * @var array			A map which defines which deserializers work with which content-types
         */
        protected $contentTypes;

        /**
         * Constructor
		 *
         * @param Slim $app
         * @param array $settings
         */
        public function __construct($app, $settings = array())
        {
            $this->app = $app;
        }

		/**
		 * Run the middleware
		 */
        public function call()
        {
			$env = $this->getApplication()->environment();

            if(isset($env['CONTENT_TYPE']))
            {
                $env['slim.input_original'] = $env['slim.input'];
                $env['slim.input'] = $this->parse($env['slim.input'], $env['CONTENT_TYPE']);
            }

            return $this->next->call();
        }
    }