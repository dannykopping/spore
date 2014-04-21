<?php

use Spore\Annotation\AbstractAnnotation;
use Spore\Container;
use Spore\Spore;

/**
 * @group routing
 */
class CustomisedTest extends PHPUnit_Framework_TestCase
{
    public function testRuntimeCustomAnnotationInjection()
    {
        $resource = new MyCustomResource();
        $spore = new Spore([$resource]);

        $container = $spore->getContainer();
        $container->extend(Container::ANNOTATION_CLASSES, function($annotations) {
            $annotations['Custom'] = 'MyCustomAnnotation';
            return $annotations;
        });

        $routes = $spore->initialise();
        $this->assertGreaterThanOrEqual(1, $routes);

        $route = $routes[0];
        $this->assertArrayHasKey(MyCustomAnnotation::getIdentifier(), $route->getAnnotations());
    }
}

/**
 * @base        /resource
 */
class MyCustomResource
{
    /**
     * @uri         /xxx
     * @custom      Hello, World!
     */
    public function myCustomAction()
    {
    }
}

class MyCustomAnnotation extends AbstractAnnotation
{
    public static function getIdentifier()
    {
        return 'custom';
    }
}