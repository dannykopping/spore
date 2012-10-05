<?php
	namespace Spore\Auth;

    use Slim\Slim;
	use Spore\Spore;
	use Spore\ReST\AutoRoute\Route;
	use Spore\Ext\Base;

	class AccessController extends Base
    {
        private $routes;

        protected static $authorizationCallback;

        public function __construct(Spore $slimInstance, $args = null)
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
			$route = $this->getSlimInstance()->router()->current();

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
						$authFailedHandler = $this->slimInstance->getAuthFailedHandler();
						if(!empty($authFailedHandler))
							call_user_func_array($authFailedHandler, array());
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