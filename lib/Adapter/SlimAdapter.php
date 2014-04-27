<?php
namespace Spore\Adapter;

use Exception;
use Slim\Route;
use Slim\Router;
use Spore\Container;
use Spore\Model\RouteModel;

/**
 * @author Danny Kopping
 */
class SlimAdapter extends BaseAdapter
{
    /**
     * Define multiple routes in the router
     *
     * @param RouteModel[] $models
     *
     * @throws \Exception
     * @return mixed
     */
    public function createRoutes($models = array())
    {
        if(!count($models)) {
            return [];
        }

        $routes = [];
        foreach($models as $model) {
            if(!$model instanceof RouteModel) {
                throw new Exception('Invalid route model supplied');
            }

            $routes[] = $this->createRoute($model);
        }

        return $routes;
    }

    /**
     * Define a route in the router
     *
     * @param RouteModel $model
     *
     * @return mixed
     */
    public function createRoute(RouteModel $model)
    {
        $container = $this->getContainer();

        /**
         * @var $router Router
         */
        $router = $this->getRouter();

        $route = new Route($model->getURI(), $model->getCallback());
        $this->setVerbs($route, $model->getVerbs());

        /**
         * Map all defined annotations to the route
         */
        foreach ($model->getAnnotations() as $annotation) {

            switch ($annotation->getIdentifier()) {

                case $container[Container::NAME_ANNOTATION]:
                    $alias = $model->getValueByAnnotation($container[Container::NAME_ANNOTATION]);
                    $this->setAlias($route, $alias);
                    break;

            }
        }

        // add route to Slim's router
        $router->map($route);
        return $route;
    }

    private function setVerbs(Route $route, array $verbs)
    {
        call_user_func_array(array($route, 'setHttpMethods'), $verbs);
    }

    private function setAlias(Route $route, $alias)
    {
        if (!empty($alias)) {
            $route->setName($alias);
        }
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return 'slim';
    }

    /**
     * Defines the expected class of the adapter's router
     *
     * @return string
     */
    public function getRouterClass()
    {
        return '\\Slim\\Router';
    }

}