<?php
namespace Spore\Adapter;

use Slim\Slim;
use Spore\Container;
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
        $container = $this->getContainer();

        /**
         * @var $adaptee Slim
         */
        $adaptee = $this->getAdaptee();
        $adapteeRoute = $adaptee->map($route->getURI(), $route->getCallback());
        call_user_func_array(array($adapteeRoute, 'setHttpMethods'), $route->getVerbs());

        $name = $route->getValueByAnnotation($container[Container::NAME_ANNOTATION]);
        if(!empty($name)) {
            $adapteeRoute->setName($name);
        }

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