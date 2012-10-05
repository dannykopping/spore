<?php
	namespace ReST\AutoRoute;

    class AutoRoutePlugin extends Slim_Plugin_Base
    {
        private $classes;

        private $routes;

        const ROUTE = "url";
        const METHODS = "methods";
        const AUTH = "auth";

        public function __construct(Slim $slimInstance, $args=null)
        {
            parent::__construct($slimInstance, $args);
            spl_autoload_register(array('AutoRoutePlugin', 'autoload'));

            $this->classes = $args;
            $this->analyzeClassesForAutoRoutes($this->classes);
        }

        public static function autoload($class)
        {
            // check same directory
            $file = realpath(dirname(__FILE__) . "/" . $class . ".php");

            // if none found, check other directories
            if (!$file)
            {
                $searchDirectories = array(dirname(__FILE__),
                                           dirname(__FILE__)."/util",
                                           dirname(__FILE__)."/phpdbl/lib/");

                foreach ($searchDirectories as $dir)
                {
                    $file = realpath($dir . "/" . $class . ".php");

                    if($file)
                        break;
                }
            }

            // if found, require_once the sucker!
            if ($file)
                require_once $file;
        }

        private function analyzeClassesForAutoRoutes($classes)
        {
            if (empty($classes))
                return;

            if (!is_array($classes))
                $classes = array($classes);

            $allRoutes = array();

            foreach ($classes as $class)
            {
                $dbp = new DocBlockParser();
                $dbp->setAllowInherited(false);
                $dbp->setMethodFilter(ReflectionMethod::IS_PUBLIC);
                $dbp->analyze($class);

                $methods = $dbp->getMethods();

                if (empty($methods))
                    continue;

                $routes = array();
                $descriptors = array();

                foreach ($methods as $method)
                {
                    $annotations = $this->buildAnnotationDescriptors($method->getAnnotations());

                    // if there is no @route annotation, ignore this method
                    if(!$method->hasAnnotation(self::ROUTE))
                        continue;

                    $descriptor = new RouteDescriptor($annotations, $method);

                    $descriptors[] = $descriptor;

                    // @ignore annotations force the AutoRouter to ignore that method
                    if ($method->hasAnnotation("ignore"))
                        continue;

                    if (!is_object($class) && !$method->getReflectionObject()->isStatic())
                    {
                        die($method->name . " is not statically accessible. Try passing " .
                            "a class instance of " . $class . " to the AutoRoute plugin " .
                            "instead of the class name.");
                    }

                    $route = $this->createRoute($method, $class, $descriptor);

                    $routes[] = $route;
                    $allRoutes[] = $route;
                }

                foreach ($routes as $route)
                {
                    if(empty($route))
                        continue;

                    $slimRoute = $this->getSlimInstance()->map($route->getUri(), $route->getCallback());
                    foreach ($route->getMethods() as $method)
                        $slimRoute->via($method);
                }
            }

            $this->routes = $allRoutes;
            $this->slimInstance->applyHook("slim.plugin.autoroute.ready", $allRoutes);
        }

        /**
         * @param array $annotations
         * @return array
         */
        private function buildAnnotationDescriptors($annotations)
        {
            if(empty($annotations))
                return;

            $descriptors = array();
            foreach($annotations as $annotation)
            {
                if(empty($annotation) || empty($annotation->name))
                    continue;

                $descriptors[] = new RouteAnnotation($annotation->name, $annotation->values);
            }

            return $descriptors;
        }

        /**
         * @param MethodElement $method
         * @param string|object $class
         * @param RouteDescriptor $descriptor
         *
         * @return Route
         */
        private function createRoute(MethodElement $method, $class, RouteDescriptor $descriptor)
        {
            $uri = $this->getRouteAnnotation($method);
            if(!$uri)
                return null;

            $httpMethods = $this->getRouteMethods($method);
            $authorizedUsers = $this->getAuthorizedUsers($method);

            $route = new Route($descriptor);
            $route->setUri($uri);
            $route->setMethods($httpMethods);
            $route->setAuthorizedUsers($authorizedUsers);
            $route->setCallback(array($class, $method->name));

            return $route;
        }

        private function getRouteAnnotation(MethodElement $method)
        {
            $routeAnnotation = $method->getAnnotation(self::ROUTE);

            if (!empty($routeAnnotation) && (empty($routeAnnotation->values) || empty($routeAnnotation->values[0])))
            {
                throw new RuntimeException("The method [" . $method->getClass()->name . "::" . $method->name . "] requires " .
                    "a value for the @".self::ROUTE." annotation. Example:\n" .
                    "/**\n" .
                    "* @".self::ROUTE." /users/get\n" .
                    "*/");
            }

            if(!empty($routeAnnotation))
                return $routeAnnotation->values[0];

            return null;
        }

        private function getRouteMethods($method)
        {
            $routeMethodsAnnotation = $method->getAnnotation(self::METHODS);

            if (!$routeMethodsAnnotation)
            {
                throw new Exception("No @".self::METHODS." annotation could be found in [" . $method->getClass()->name . "::" . $method->name . "]. " .
                    "This annotation is required for routing. " .
                    "Add a @ignore annotation to exclude this method from auto-routing");
            }

            if (empty($routeMethodsAnnotation->values) || empty($routeMethodsAnnotation->values[0]))
            {
                throw new RuntimeException("The method [" . $method->getClass()->name . "::" . $method->name . "] requires " .
                    "a value for the @".self::METHODS." annotation. Example:\n" .
                    "/**\n" .
                    "* @".self::METHODS."   GET,POST\n" .
                    "*/");
            }

            return explode(",", $routeMethodsAnnotation->values[0]);
        }

        private function getAuthorizedUsers($method)
        {
            $authorizeAnnotation = $method->getAnnotation(self::AUTH);

            // check for spelling errors
            if(empty($authorizeAnnotation))
                $authorizeAnnotation = $method->getAnnotation(self::AUTH);

            if(empty($authorizeAnnotation))
                return null;

            if (empty($authorizeAnnotation->values) || empty($authorizeAnnotation->values[0]))
            {
                throw new RuntimeException("The method [" . $method->getClass()->name . "::" . $method->name . "] requires " .
                    "a value for the @".self::AUTH." annotation. Example:\n" .
                    "/**\n" .
                    "* @".self::AUTH."	user,admin\n" .
                    "*/");
            }

            return explode(",", $authorizeAnnotation->values[0]);
        }
    }

?>