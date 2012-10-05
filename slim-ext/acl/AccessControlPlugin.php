<?php
    class AccessControlPlugin extends Slim_Plugin_Base
    {
        private $routes;

        protected static $authorizationCallback;

        public function __construct(Slim $slimInstance, $args = null)
        {
            parent::__construct($slimInstance, $args);

            $this->slimInstance->hook("slim.plugin.autoroute.ready", array($this, "routesReadyHandler"));
            $this->slimInstance->hook("slim.before.dispatch", array($this, "checkAuthorizationForRoute"));
        }

        public function routesReadyHandler($routes)
        {
            $this->routes = $routes;
        }

        public function checkAuthorizationForRoute($route)
        {
            $callable = $route->getCallable();
            $authCallback = self::getAuthorizationCallback();

            if (empty($authCallback))
                return;

            // if no AutoRoutes are available, call the callable (this plugin only works with AutoRoutes)
            if (!$this->routes)
                return;

            foreach ($this->routes as $route)
            {
                if (empty($route))
                    continue;

                // check that the auto-route's callable is the same as the pending route's callable
                if ($route->getCallback() === $callable)
                {
                    $authorizedUsers = $route->getAuthorizedUsers();
                    if (empty($authorizedUsers))
                        return;

                    $authorized = call_user_func_array(self::getAuthorizationCallback(), array($authorizedUsers));

                    if (!$authorized)
                    {
                        $this->slimInstance->contentType("application/json");
                        $this->slimInstance->response()->header("Access-Control-Allow-Origin", "*");

                        $this->slimInstance->halt(401,
                            json_encode(array(
                                             "message" => "You are not authorized to execute this function",
                                             "code"    => 401
                                        )));
                    }
                }
            }
        }

        public static function authorizationCallback($callable)
        {
            self::$authorizationCallback = $callable;
        }

        public static function getAuthorizationCallback()
        {
            return self::$authorizationCallback;
        }

    }

?>