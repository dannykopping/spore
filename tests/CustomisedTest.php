<?php

use Spore\Annotation\AbstractAnnotation;
use Spore\Container;
use Spore\Spore;

/**
 * @group routing
 */
class CustomisedTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that injecting more annotations at runtime works seamlessly
     */
    public function testRuntimeCustomAnnotationInjection()
    {
        $spore = new Spore([new MyCustomResource()]);

        $container = $spore->getContainer();
        $container->extend(Container::ANNOTATION_CLASSES, function($annotations) {
            $annotations['Custom'] = 'MyCustomAnnotation';
            return $annotations;
        });

        $routes = $spore->getRoutes();
        $this->assertNotEmpty($routes);

        $route = current($routes);
        $this->assertArrayHasKey(MyCustomAnnotation::getIdentifier(), $route->getAnnotations());
    }

    /**
     * Test overriding of prerequisite annotations that define a valid route
     */
    public function testRuntimePrerequisiteOverride()
    {
        $spore = new Spore([new MyCustomResource2()]);

        $container = $spore->getContainer();
        $container->extend(Container::ANNOTATION_CLASSES, function($annotations) {
            $annotations['Custom'] = 'MyCustomAnnotation';
            return $annotations;
        });

        $container = $spore->getContainer();
        $container->extend(Container::PREREQUISITE_ANNOTATIONS, function() {
            return [];
        });

        $routes = $spore->getRoutes();
        $this->assertNotEmpty($routes);
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

class MyCustomResource2
{
    /**
     * Look ma, no @uri annotation which is normally a prerequisite!
     *
     * @custom
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