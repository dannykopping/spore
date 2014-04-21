<?php
namespace Spore;

use DocBlock\Parser;
use Pimple;
use ReflectionMethod;
use Spore\Factory\AnnotationFactory;
use Spore\Model\Route;
use Spore\Service\RouteInspectorService;

/**
 * @author Danny Kopping
 */
class Container extends Pimple
{
    const DOCBLOCK_PARSER          = 'docblock-parser';
    const ANNOTATION_FACTORY       = 'annotation-factory';
    const ANNOTATION_CLASSES       = 'annotation-classes';
    const PREREQUISITE_ANNOTATIONS = 'prerequisite-annotations';
    const ROUTE_INSPECTOR          = 'route-inspector';
    const BEFORE_CALLBACK          = 'before-callback';
    const CALLBACK_WRAPPER         = 'callback-wrapper';
    const AFTER_CALLBACK           = 'after-callback';

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
         * @return \Spore\Factory\AnnotationFactory
         */
        $this[self::ANNOTATION_FACTORY] = function () {
            return new AnnotationFactory($this);
        };

        $this[self::ANNOTATION_CLASSES] = function () {
            return [
                'uri'  => '\\Spore\\Annotation\\URI',
                'base' => '\\Spore\\Annotation\\Base',
            ];
        };

        /**
         * @return array
         */
        $this[self::PREREQUISITE_ANNOTATIONS] = function () {
            return ['uri'];
        };

        /**
         * @return RouteInspectorService
         */
        $this[self::ROUTE_INSPECTOR] = function () {
            return new RouteInspectorService($this);
        };

        /**
         * @return callable
         */
        $this[self::BEFORE_CALLBACK] = function () {
            return function (Route $route) {
                return;
            };
        };

        /**
         * @return callable
         */
        $this[self::CALLBACK_WRAPPER] = function () {
            return function (Route $route) {
                return call_user_func_array($route->getCallback(), [$route]);
            };
        };

        /**
         * @return callable
         */
        $this[self::AFTER_CALLBACK] = function () {
            return function (Route $route, $result = null) {
                return;
            };
        };
    }
} 