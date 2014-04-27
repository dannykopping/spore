<?php
namespace Spore;

use DocBlock\Parser;
use Pimple;
use ReflectionMethod;
use Spore\Factory\AdapterFactory;
use Spore\Factory\AnnotationFactory;
use Spore\Model\RouteModel;
use Spore\Service\RouteInspectorService;

/**
 * @author Danny Kopping
 */
class Container extends Pimple
{
    const BASE_ANNOTATION  = 'base-annotation';
    const URI_ANNOTATION   = 'uri-annotation';
    const VERB_ANNOTATION  = 'verb-annotation';
    const VERBS_ANNOTATION = 'verbs-annotation';
    const NAME_ANNOTATION  = 'name-annotation';

    const DOCBLOCK_PARSER = 'docblock-parser';
    const ROUTE_INSPECTOR = 'route-inspector';
    const ROUTE_MODEL     = 'route-model';

    const ADAPTER_FACTORY = 'adapter-factory';
    const ADAPTER_CLASSES = 'adapter-classes';

    const ANNOTATION_FACTORY       = 'annotation-factory';
    const ANNOTATION_CLASSES       = 'annotation-classes';
    const PREREQUISITE_ANNOTATIONS = 'prerequisite-annotations';

    const BEFORE_CALLBACK = 'before-callback';
    const AFTER_CALLBACK  = 'after-callback';

    const CURRENT_ROUTE = 'current-route';


    public function initialise()
    {
        $this[self::BASE_ANNOTATION]  = 'base';
        $this[self::URI_ANNOTATION]   = 'uri';
        $this[self::VERB_ANNOTATION]  = 'verb';
        $this[self::VERBS_ANNOTATION] = 'verbs';
        $this[self::NAME_ANNOTATION]  = 'name';

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

        /**
         * @return array
         */
        $this[self::ANNOTATION_CLASSES] = function () {
            return [
                $this[self::BASE_ANNOTATION]  => '\\Spore\\Annotation\\BaseAnnotation',
                $this[self::URI_ANNOTATION]   => '\\Spore\\Annotation\\URIAnnotation',
                $this[self::VERB_ANNOTATION]  => '\\Spore\\Annotation\\VerbsAnnotation',
                $this[self::VERBS_ANNOTATION] => '\\Spore\\Annotation\\VerbsAnnotation',
                $this[self::NAME_ANNOTATION]  => '\\Spore\\Annotation\\NameAnnotation',
            ];
        };

        /**
         * @return array
         */
        $this[self::PREREQUISITE_ANNOTATIONS] = function () {
            return [
                $this[self::URI_ANNOTATION],
            ];
        };

        /**
         * @return RouteInspectorService
         */
        $this[self::ROUTE_INSPECTOR] = function () {
            return new RouteInspectorService($this);
        };

        $this[self::ROUTE_MODEL] = '\\Spore\\Model\\RouteModel';

        /**
         * @return callable
         */
        $this[self::BEFORE_CALLBACK] = function () {
            return function (RouteModel $route) {
                return;
            };
        };

        /**
         * @return callable
         */
        $this[self::AFTER_CALLBACK] = function () {
            return function (RouteModel $route, $result = null) {
                return;
            };
        };

        /**
         * @return array
         */
        $this[self::ADAPTER_CLASSES] = function () {
            return [
                'slim' => '\\Spore\\Adapter\\SlimAdapter',
            ];
        };

        /**
         * @return \Spore\Factory\AnnotationFactory
         */
        $this[self::ADAPTER_FACTORY] = function () {
            return new AdapterFactory($this);
        };

        $this[self::CURRENT_ROUTE] = null;
    }
} 