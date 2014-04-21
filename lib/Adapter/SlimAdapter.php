<?php
namespace Spore\Adapter;

use Slim\Http\Request;
use Slim\Slim;
use Spore\Model\Route;

/**
 * @author Danny Kopping
 */
class SlimAdapter extends BaseAdapter
{
    /**
     * @param Route $route
     *
     * @return \Slim\Route
     */
    public function createRoute(Route $route)
    {
        /**
         * @var $adaptee Slim
         */
        $adaptee = $this->getAdaptee();
        $adapteeRoute = $adaptee->map($route->getURI(), $route->getCallback());
        call_user_func_array(array($adapteeRoute, 'setHttpMethods'), $route->getVerbs());

        return $adapteeRoute;
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return 'slim';
    }
}