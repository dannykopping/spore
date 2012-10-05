<?php
    /**
     *
     */
    class TestService
    {
        /**
         * @url         /test
         * @methods     GET,POST
         */
        public function sayHello(Request $req, Response $response)
        {
			$response->status = Status::OK;
			$response->headers['X-Powered-By'] = "Aerial REST";

            return "Hello :)";
        }
    }
