<?php
namespace Spore\ReST\AutoRoute;

use Spore\Ext\Base;
use DocBlock\Element\AnnotationElement;
use Exception;
use DocBlock\Element\MethodElement;
use Spore\ReST\AutoRoute\Util\RouteAnnotation;
use Spore\ReST\AutoRoute\Util\RouteDescriptor;
use ReflectionMethod;
use DocBlock\Parser;
use RuntimeException;
use Slim\Slim;

/**
 *
 */
class AutoRouter extends Base
{
    /**
     * @var array                An array of classes to be analyzed for auto-routes
     */
    private $classes;

    /**
     *    The @name attribute
     */
    const NAME = "name";

    /**
     *    The @url attribute
     */
    const ROUTE = "url";

    /**
     *    The @verbs attribute
     */
    const VERBS = "verbs";

    /**
     *    The @auth attribute
     */
    const AUTH = "auth";

    /**
     *    The @template attribute
     */
    const TEMPLATE = "template";

    /**
     *    The @render attribute
     */
    const RENDER = "render";

    /**
     *    The @conditions attribute
     */
    const CONDITION = "condition";

    /**
     * Constructor
     *
     * @param \Slim\Slim $slimInstance
     * @param null       $args
     */
    public function __construct(Slim $slimInstance, $args = null)
    {
        parent::__construct($slimInstance, $args);

        $this->classes = $args;
        $this->analyzeClassesForAutoRoutes($this->classes);
    }

    /**
     * Analyze the array of provided classes for auto-route annotations
     *
     * @param $classes
     *
     * @throws \Exception
     */
    private function analyzeClassesForAutoRoutes($classes)
    {
        if (empty($classes)) {
            return;
        }

        if (!is_array($classes)) {
            $classes = array($classes);
        }

        $allRoutes = array();

        foreach ($classes as $class) {
            // create the DocBlock parser
            $dbp = new Parser();
            $dbp->setAllowInherited(false);
            $dbp->setMethodFilter(ReflectionMethod::IS_PUBLIC); // only inspect public methods
            $dbp->analyze($class);

            $methods = $dbp->getMethods();

            if (empty($methods)) {
                continue;
            }

            $routes = array();
            $descriptors = array();

            foreach ($methods as $method) {
                $annotations = $this->buildAnnotationDescriptors($method->getAnnotations());

                // if there is no @url annotation, ignore this method
                if (!$method->hasAnnotation(self::ROUTE)) {
                    continue;
                }

                $descriptor = new RouteDescriptor($annotations, $method);
                $descriptors[] = $descriptor;

                // @ignore annotations force the AutoRouter to ignore that method
                if ($method->hasAnnotation("ignore")) {
                    continue;
                }

                // if the auto-route callable cannot be accessed, an exception is thrown
                if (!is_object($class) && !$method->getReflectionObject()->isStatic()) {
                    throw new Exception($method->name . " is not statically accessible. Try passing " .
                            "a class instance of " . $class . " to the AutoRoute plugin " .
                            "instead of the class name.");
                }

                // create the auto-route
                $route = $this->createRoute($method, $class, $descriptor);

                $routes[] = $route;
                $allRoutes[] = $route;
            }

            foreach ($routes as $route) {
                if (empty($route)) {
                    continue;
                }

                // create Slim routes for the auto-route
                $slimRoute = $this->getSlimInstance()->map($route->getUri(), $route->getCallback());
                foreach ($route->getMethods() as $method) {
                    $slimRoute->via($method);
                }

                $name = $route->getName();
                if (!empty($name)) {
                    $slimRoute->name($name);
                }

                $conditions = $this->getConditionValues($route->getConditions());
                if (!empty($conditions) && count($conditions) > 0) {
                    $slimRoute->conditions($conditions);
                }
            }
        }

        // set the globally accessible list of auto-routes
        $this->getSlimInstance()->routes = $allRoutes;
        $this->slimInstance->applyHook("spore.autoroute.ready", $allRoutes);
    }

    /**
     * Build the route annotation descriptors
     *
     * @param array $annotations
     *
     * @return array
     */
    private function buildAnnotationDescriptors($annotations)
    {
        if (empty($annotations)) {
            return;
        }

        $descriptors = array();
        foreach ($annotations as $annotation) {
            if (empty($annotation) || empty($annotation->name)) {
                continue;
            }

            $descriptors[] = new RouteAnnotation($annotation->name, $annotation->values);
        }

        return $descriptors;
    }

    /**
     * Create the auto-route
     *
     * @param MethodElement   $method
     * @param string|object   $class
     * @param RouteDescriptor $descriptor
     *
     * @return Route
     */
    private function createRoute(MethodElement $method, $class, RouteDescriptor $descriptor)
    {
        $uri = $this->getRouteAnnotation($method);
        if (!$uri) {
            return null;
        }

        $name = $this->getNameAnnotation($method);
        $httpMethods = $this->getRouteMethods($method);
        $authorizedUsers = $this->getAuthorizedUsers($method);
        $template = $this->getTemplateAnnotation($method);
        $render = $this->getRenderAnnotation($method);
        $conditions = $this->getConditionAnnotations($method);

        // set the auto-route properties based on the provided annotations
        $route = new Route($descriptor);
        $route->setName($name);
        $route->setUri($uri);
        $route->setMethods($httpMethods);
        $route->setAuthorizedUsers($authorizedUsers);
        $route->setTemplate($template);
        $route->setRender($render);
        $route->setCallback(array($class, $method->name));
        $route->setConditions($conditions);

        return $route;
    }

    /**
     * Get the @url annotation value for a particular auto-route callable
     *
     * @param \DocBlock\Element\MethodElement $method
     *
     * @return null
     * @throws \RuntimeException
     */
    private function getRouteAnnotation(MethodElement $method)
    {
        $routeAnnotation = $method->getAnnotation(self::ROUTE);

        if (!empty($routeAnnotation) && (empty($routeAnnotation->values) || empty($routeAnnotation->values[0]))) {
            throw new RuntimeException("The method [" . $method->getClass(
            )->name . "::" . $method->name . "] requires " .
                    "a value for the @" . self::ROUTE . " annotation. Example:\n" .
                    "/**\n" .
                    "* @" . self::ROUTE . " /users/get\n" .
                    "*/");
        }

        if (!empty($routeAnnotation)) {
            return $routeAnnotation->values[0];
        }

        return null;
    }

    /**
     * Get the @verbs annotation value for a particular auto-route callable
     *
     * @param $method
     *
     * @return array
     * @throws \RuntimeException
     * @throws \Exception
     */
    private function getRouteMethods($method)
    {
        $routeMethodsAnnotation = $method->getAnnotation(self::VERBS);

        if (!$routeMethodsAnnotation) {
            throw new Exception("No @" . self::VERBS . " annotation could be found in [" . $method->getClass(
            )->name . "::" . $method->name . "]. " .
                    "This annotation is required for routing. " .
                    "Add a @ignore annotation to exclude this method from auto-routing");
        }

        if (empty($routeMethodsAnnotation->values) || empty($routeMethodsAnnotation->values[0])) {
            throw new RuntimeException("The method [" . $method->getClass(
            )->name . "::" . $method->name . "] requires " .
                    "a value for the @" . self::VERBS . " annotation. Example:\n" .
                    "/**\n" .
                    "* @" . self::VERBS . "   GET,POST\n" .
                    "*/");
        }

        return explode(",", $routeMethodsAnnotation->values[0]);
    }

    /**
     * Get the @auth annotation value for a particular auto-route callable
     *
     * @param $method
     *
     * @return array|null
     * @throws \RuntimeException
     */
    private function getAuthorizedUsers($method)
    {
        $authorizeAnnotation = $method->getAnnotation(self::AUTH);

        // check for spelling errors
        if (empty($authorizeAnnotation)) {
            $authorizeAnnotation = $method->getAnnotation(self::AUTH);
        }

        if (empty($authorizeAnnotation)) {
            return null;
        }

        if (empty($authorizeAnnotation->values) || empty($authorizeAnnotation->values[0])) {
            throw new RuntimeException("The method [" . $method->getClass(
            )->name . "::" . $method->name . "] requires " .
                    "a value for the @" . self::AUTH . " annotation. Example:\n" .
                    "/**\n" .
                    "* @" . self::AUTH . "	user,admin\n" .
                    "*/");
        }

        return explode(",", $authorizeAnnotation->values[0]);
    }

    /**
     * Get the @template annotation value for a particular auto-route callable
     *
     * @param \DocBlock\Element\MethodElement $method
     *
     * @return string
     */
    private function getTemplateAnnotation(MethodElement $method)
    {
        $templateAnnotation = $method->getAnnotation(self::TEMPLATE);

        if (empty($templateAnnotation) || count($templateAnnotation->values) < 1) {
            return null;
        }

        return $templateAnnotation->values[0];
    }

    /**
     * Get the @render annotation value for a particular auto-route callable
     *
     * @param $method
     *
     * @return string
     */
    private function getRenderAnnotation($method)
    {
        $renderAnnotation = $method->getAnnotation(self::RENDER);

        if (empty($renderAnnotation) || count($renderAnnotation->values) < 1) {
            return "always";
        }

        return $renderAnnotation->values[0];
    }

    /**
     * Get the @name annotation value for a particular auto-route callable
     *
     * @param $method
     *
     * @return string
     */
    private function getNameAnnotation($method)
    {
        $nameAnnotation = $method->getAnnotation(self::NAME);

        if (empty($nameAnnotation) || count($nameAnnotation->values) < 1) {
            return null;
        }

        return $nameAnnotation->values[0];
    }

    /**
     * Get the @condition annotation values for a particular auto-route callable
     *
     * @param $method
     *
     * @return array|null
     */
    private function getConditionAnnotations($method)
    {
        $conditionAnnotations = $method->getAnnotations(array(self::CONDITION));
        if (empty($conditionAnnotations) || count($conditionAnnotations) < 1) {
            return null;
        }

        return $conditionAnnotations;
    }

    /**
     * Returns a key-pair array of route conditions
     *
     * @param array $conditions
     *
     * @return array|null
     */
    private function getConditionValues($conditions)
    {
        if (empty($conditions) || count($conditions) <= 0) {
            return null;
        }

        $routeConditions = array();
        foreach ($conditions as $condition) {
            $value = !empty($condition->values) && count($condition->values) == 2 ? $condition->values : null;

            $param = trim($value[0]);
            $regex = trim($value[1]);
            $routeConditions[$param] = $regex;
        }

        return $routeConditions;
    }
}

?>
