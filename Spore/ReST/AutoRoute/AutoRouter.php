<?php
	namespace Spore\ReST\AutoRoute;

    use Spore\Ext\Base;
	use Exception;
	use DocBlock\Element\MethodElement;
	use Spore\ReST\AutoRoute\Util\RouteAnnotation;
	use Spore\ReST\AutoRoute\Util\RouteDescriptor;
	use ReflectionMethod;
	use DocBlock\Parser;
	use RuntimeException;
	use Slim\Slim;

	class AutoRouter extends Base
    {
        private $classes;

        private $routes;

        const ROUTE = "url";
        const VERBS = "verbs";
        const AUTH = "auth";
        const TEMPLATE = "template";
        const RENDER = "render";

        public function __construct(Slim $slimInstance, $args=null)
        {
            parent::__construct($slimInstance, $args);

            $this->classes = $args;
            $this->analyzeClassesForAutoRoutes($this->classes);
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
                $dbp = new Parser();
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

                    // if there is no @url annotation, ignore this method
                    if(!$method->hasAnnotation(self::ROUTE))
                        continue;

                    $descriptor = new RouteDescriptor($annotations, $method);

                    $descriptors[] = $descriptor;

                    // @ignore annotations force the AutoRouter to ignore that method
                    if ($method->hasAnnotation("ignore"))
                        continue;

                    if (!is_object($class) && !$method->getReflectionObject()->isStatic())
                    {
                        throw new Exception($method->name . " is not statically accessible. Try passing " .
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

			$this->getSlimInstance()->routes = $allRoutes;
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
            $template = $this->getTemplateAnnotation($method);
            $render = $this->getRenderAnnotation($method);

            $route = new Route($descriptor);
            $route->setUri($uri);
            $route->setMethods($httpMethods);
            $route->setAuthorizedUsers($authorizedUsers);
			$route->setTemplate($template);
			$route->setRender($render);
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
            $routeMethodsAnnotation = $method->getAnnotation(self::VERBS);

            if (!$routeMethodsAnnotation)
            {
                throw new Exception("No @".self::VERBS." annotation could be found in [" . $method->getClass()->name . "::" . $method->name . "]. " .
                    "This annotation is required for routing. " .
                    "Add a @ignore annotation to exclude this method from auto-routing");
            }

            if (empty($routeMethodsAnnotation->values) || empty($routeMethodsAnnotation->values[0]))
            {
                throw new RuntimeException("The method [" . $method->getClass()->name . "::" . $method->name . "] requires " .
                    "a value for the @".self::VERBS." annotation. Example:\n" .
                    "/**\n" .
                    "* @".self::VERBS."   GET,POST\n" .
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

        private function getTemplateAnnotation(MethodElement $method)
        {
            $templateAnnotation = $method->getAnnotation(self::TEMPLATE);

            if(empty($templateAnnotation) || count($templateAnnotation->values) < 1)
				return null;

			return $templateAnnotation->values[0];
        }

		private function getRenderAnnotation($method)
		{
            $renderAnnotation = $method->getAnnotation(self::RENDER);

            if(empty($renderAnnotation) || count($renderAnnotation->values) < 1)
				return "always";

			return $renderAnnotation->values[0];
		}
	}

?>