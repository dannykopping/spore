<?php
namespace Spore\Auth;

use Slim\Slim;
use Spore\Spore;
use Spore\ReST\AutoRoute\Route;
use Spore\Ext\Base;

/**
 *    This class controls the authorization mechanism
 */
class AccessController extends Base
{
    /**
     * @var    array         An array of auto-routes
     */
    private $routes;

    /**
     * @var    callable    The callback to execute when an authorization request is initiated
     */
    protected static $authorizationCallback;

    /**
     * Constructor
     *
     * @param \Spore\Spore $slimInstance
     * @param null         $args
     */
    public function __construct(Spore $slimInstance, $args = null)
    {
        parent::__construct($slimInstance, $args);

        // apply Slim hooks
        $this->getSlimInstance()->hook("spore.autoroute.ready", array($this, "routesReadyHandler"));
        $this->getSlimInstance()->hook("slim.before.dispatch", array($this, "checkAuthorizationForRoute"));
    }

    /**
     * Once the auto-routes are ready, keep track of them in this class
     *
     * @param $routes
     */
    public function routesReadyHandler($routes)
    {
        $this->routes = $routes;
    }

    /**
     * Determine whether the requested auto-route is accessible based on authorization role
     *
     * @param $route
     */
    public function checkAuthorizationForRoute($route)
    {
        $route = $this->getSlimInstance()->router()->getCurrentRoute();
        $router = $this->getSlimInstance()->router();
        $params = $route->getParams();

        list($request, $response) = $router->getRequestAndResponseObjects($route, $params);

        $callable = $route->getCallable();
        $authCallback = self::getAuthorizationCallback();

        if (empty($authCallback)) {
            return;
        }

        // if no AutoRoutes are available, call the callable (this plugin only works with AutoRoutes)
        if (!$this->routes) {
            return;
        }

        // find the relevant auto-route
        foreach ($this->routes as $route) {
            if (empty($route)) {
                continue;
            }

            // check that the auto-route's callable is the same as the pending route's callable
            if ($route->getCallback() === $callable) {
                $authorizedUsers = $route->getAuthorizedUsers();

                // if no authorization annotation has been defined, don't bother with authorization
                if (empty($authorizedUsers)) {
                    return;
                }

                $authorized = call_user_func_array(
                    self::getAuthorizationCallback(),
                    array($authorizedUsers, $request, $response)
                );

                // if the defined role is not authorized, call the "authorization failed" handler
                if (!$authorized) {
                    $authFailedHandler = $this->getSlimInstance()->authFailed();
                    if (!empty($authFailedHandler)) {
                        call_user_func_array($authFailedHandler, array());
                    }
                }
            }
        }
    }

    /**
     * Set the "authorization failed" handler
     *
     * @param $callable
     */
    public static function authorizationCallback($callable)
    {
        self::$authorizationCallback = $callable;
    }

    /**
     * Get the "authorization failed" handler
     *
     * @return mixed
     */
    public static function getAuthorizationCallback()
    {
        return self::$authorizationCallback;
    }

}

?>
