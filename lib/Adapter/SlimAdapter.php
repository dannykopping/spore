<?php
namespace Spore\Adapter;

use Slim\Route;
use Slim\Slim;
use Spore\Container;
use Spore\Model\RouteModel;

/**
 * @author Danny Kopping
 */
class SlimAdapter extends BaseAdapter
{
    /**
     * Define a route in the adaptee
     *
     * @param RouteModel $model
     *
     * @return mixed
     */
    public function createRoute(RouteModel $model)
    {
        $container = $this->getContainer();

        /**
         * @var $adaptee Slim
         */
        $adaptee = $this->getAdaptee();

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
        $adaptee->router->map($route);
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
}