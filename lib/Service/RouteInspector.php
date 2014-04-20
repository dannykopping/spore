<?php
namespace Spore\Service;

use DocBlock\Element\Base as BaseAnnotationElement;
use DocBlock\Element\MethodElement;
use DocBlock\Parser;
use ReflectionMethod;
use Spore\Annotation\URI;
use Spore\Container;
use Spore\Factory\Annotation;
use Spore\Model\Route;

/**
 * @author Danny Kopping
 */
class RouteInspector extends Base
{
    /**
     * @var array
     */
    protected $targets;

    public function run()
    {
        $parser  = $this->getParser();
        $targets = $this->getTargets();

        $parser->analyze($targets);

        $routableMethods = $this->getRoutableMethods($parser);
        if (!count($routableMethods)) {
            return [];
        }

        $routes = [];
        foreach ($routableMethods as $method) {
            $class    = $method->getClass();
            $instance = $class->getInstance();

            // retrieve all recognised annotations from the resource-level (class) and action-level (method)
            $routeAnnotations = $this->getRecognisedAnnotations([$class, $method]);

            /**
             * @var $methodReflector ReflectionMethod
             */
            $methodReflector = $method->getReflectionObject();
            $route           = new Route($methodReflector->getClosure($instance), $this->getContainer(), $routeAnnotations);

            $routes[] = $route;
        }

        return $routes;
    }

    /**
     * Return an array of all methods that have at least one method with a URI annotation (a prerequisite annotation)
     *
     * @param Parser $parser
     *
     * @return MethodElement[]
     */
    protected function getRoutableMethods(Parser $parser)
    {
        if (empty($parser)) {
            return [];
        }

        $methods = $parser->getMethods();
        if (!count($methods)) {
            return [];
        }

        $routable = [];

        foreach ($methods as $method) {
            if (!$this->isMethodRoutable($method)) {
                continue;
            }

            $routable[] = $method;
        }

        return $routable;
    }

    /**
     * Determines if this callable method is routable (i.e. has a URI annotation which is a prerequisite)
     *
     * @param MethodElement $methodAnnotation
     *
     * @return bool
     */
    protected function isMethodRoutable(MethodElement $methodAnnotation)
    {
        if (empty($methodAnnotation)) {
            return false;
        }

        return $methodAnnotation->hasAnnotation(URI::getIdentifier());
    }

    /**
     * Returns an array of annotations that have related class definitions
     *
     * @param BaseAnnotationElement[] $annotationContainers
     *
     * @return \Spore\Annotation\AbstractAnnotation[]
     */
    protected function getRecognisedAnnotations(array $annotationContainers)
    {
        if (!count($annotationContainers)) {
            return [];
        }

        $recognised = [];
        foreach ($annotationContainers as $annotationContainer) {
            if (!$annotationContainer instanceof BaseAnnotationElement) {
                continue;
            }

            $annotations = $annotationContainer->getAnnotations();
            if (!count($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                $annotation = $this->getAnnotationFactory()->createByElement($annotation);
                if (empty($annotation)) {
                    continue;
                }

                $recognised[] = $annotation;
            }
        }

        return $recognised;
    }

    /**
     * @return Parser
     */
    protected function getParser()
    {
        $container = $this->getContainer();
        return $container[Container::DOCBLOCK_PARSER];
    }

    /**
     * @return Annotation
     */
    protected function getAnnotationFactory()
    {
        $container = $this->getContainer();
        return $container[Container::ANNOTATION_FACTORY];
    }

    /**
     * @param array $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return $this->targets;
    }
}