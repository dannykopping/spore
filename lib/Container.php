<?php
namespace Spore;

use DocBlock\Parser;
use Pimple;
use ReflectionMethod;
use Spore\Factory\Annotation;
use Spore\Model\Route;
use Spore\Service\RouteInspector;

/**
 * @author Danny Kopping
 */
class Container extends Pimple
{
    const DOCBLOCK_PARSER    = 'docblock-parser';
    const ANNOTATION_FACTORY = 'annotation-factory';
    const ANNOTATION_CLASSES = 'annotation-classes';
    const ROUTE_INSPECTOR    = 'route-inspector';
    const BEFORE_CALLBACK    = 'before-callback';
    const CALLBACK_WRAPPER   = 'callback-wrapper';
    const AFTER_CALLBACK     = 'after-callback';

    public function initialise()
    {
        /**
         * @return Parser
         */
        $this[self::DOCBLOCK_PARSER] = function () {
            $parser = new Parser();
            $parser->setAllowInherited(false);
            $parser->setMethodFilter(ReflectionMethod::IS_PUBLIC);

            return $parser;
        };

        /**
         * @return \Spore\Factory\Annotation
         */
        $this[self::ANNOTATION_FACTORY] = function () {
            return new Annotation($this);
        };

        /**
         * @return array
         */
        $this[self::ANNOTATION_CLASSES] = function () {
            return [
                'URI'  => '\\Spore\\Annotation\\URI',
                'Base' => '\\Spore\\Annotation\\Base',
            ];
        };

        /**
         * @return RouteInspector
         */
        $this[self::ROUTE_INSPECTOR] = function () {
            return new RouteInspector($this);
        };

        /**
         * @return callable
         */
        $this[self::BEFORE_CALLBACK] = function () {
            return function (Route $route) {
                echo 'Before -> ' . $route->getAnnotationByName('uri');
            };
        };

        /**
         * @return callable
         */
        $this[self::AFTER_CALLBACK] = function () {
            return function (Route $route, $result = null) {
                echo 'After -> ' . $route->getAnnotationByName('uri').PHP_EOL.var_export($result, true);
            };
        };
    }
} 