<?php
namespace Spore\ReST\AutoRoute\Util;

use DocBlock\Element\MethodElement;

/**
 *    A class to manage an auto-route's annotations, related reflection method and other relevant data
 */
class RouteDescriptor
{
    /**
     * @var array                                An array of annotations
     */
    private $_annotations;

    /**
     * @var \DocBlock\Element\MethodElement        Related method element
     */
    private $_method;

    /**
     * @var mixed                                Related Reflection method
     */
    private $_reflectionMethod;

    /**
     * Constructor
     *
     * @param array $annotations
     * @param MethodElement $method
     */
    public function __construct($annotations, MethodElement $method)
    {
        $this->_annotations = $annotations;
        $this->_reflectionMethod = $method->getReflectionObject();
        $this->_method = $method;
    }

    /**
     * Annotations property setter
     *
     * @param $annotations
     */
    public function setAnnotations($annotations)
    {
        $this->_annotations = $annotations;
    }

    /**
     * Annotations property getter
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->_annotations;
    }

    /**
     * Reflection method property setter
     *
     * @param $reflectionMethod
     */
    public function setReflectionMethod($reflectionMethod)
    {
        $this->_reflectionMethod = $reflectionMethod;
    }

    /**
     * Reflection method property getter
     *
     * @return mixed
     */
    public function getReflectionMethod()
    {
        return $this->_reflectionMethod;
    }

    /**
     * Method element property setter
     *
     * @param $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Method element property getter
     *
     * @return \DocBlock\Element\MethodElement
     */
    public function getMethod()
    {
        return $this->_method;
    }
}

?>
