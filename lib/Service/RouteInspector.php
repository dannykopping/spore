<?php
namespace Spore\Service;

use DocBlock\Element\MethodElement;
use DocBlock\Parser;
use ReflectionMethod;
use Spore\Annotation\Factory;
use Spore\Exception\TargetException;

/**
 * @author Danny Kopping
 */
class RouteInspector extends Base
{
    /**
     * @var array
     */
    protected $targetMap;

    public function run()
    {
        $parser = new Parser();
        $parser->setAllowInherited(false);
        $parser->setMethodFilter(ReflectionMethod::IS_PUBLIC);

        $targetMap = $this->getTargetMap();
        $parser->analyze($targetMap);

        $annotations = [];
        foreach ($parser->getMethods() as $method) {
            /**
             * @var $method MethodElement
             */
            foreach ($method->getAnnotations() as $annotation) {
                $annotation = Factory::createAnnotationByElement($annotation);
                if (empty($annotation)) {
                    continue;
                }


                /**
                 * @var $meth ReflectionMethod
                 */
                $meth = $method->getReflectionObject();

                $annotations[$method->getName()] = $annotation;
            }
        }

        var_dump($annotations);
        die();
    }

    /**
     * @param array $targetMap
     */
    public function setTargetMap($targetMap)
    {
        $this->targetMap = $targetMap;
    }

    /**
     * @return array
     */
    public function getTargetMap()
    {
        return $this->targetMap;
    }
}