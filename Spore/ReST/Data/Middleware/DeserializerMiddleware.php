<?php
	namespace Spore\ReST\Data\Middleware;

	use Slim\Slim;
	use Slim\Middleware;

    class DeserializerMiddleware extends Middleware
    {
        /**
         * @var Slim
         */
        protected $app;

        /**
         * @var array
         */
        protected $contentTypes;

        /**
         * Constructor
         * @param Slim $app
         * @param array $settings
         */
        public function __construct($app, $settings = array())
        {
            $this->app = $app;
        }

		/**
		 * Call
		 *
		 * @internal param array $env
		 * @return  array[status, header, body]
		 */
        public function call()
        {
			$env = $this->getApplication()->environment();

            if(isset($env['CONTENT_TYPE']))
            {
                $env['slim.input_original'] = $env['slim.input'];
                $env['slim.input'] = $this->parse($env['slim.input'], $env['CONTENT_TYPE']);
            }
        }
    }