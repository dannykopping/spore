<?php
namespace Spore\Adapter;

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
        $adapteeRoute = new \Slim\Route($route->getURI(), $route->getCallback());
        $adapteeRoute->setHttpMethods('GET');

        $adaptee->router()->map($adapteeRoute);
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