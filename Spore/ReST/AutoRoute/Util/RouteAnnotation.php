<?php
namespace Spore\ReST\AutoRoute\Util;

/**
 *    A class to manage an annotation in an auto-route
 */
class RouteAnnotation
{
    /**
     * @var    string            The annotation name
     */
    private $_name;
    /**
     * @var mixed            The annotation value
     */
    private $_value;

    /**
     * Constructor
     *
     * @param $name
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->_name = $name;
        $this->_value = $value;
    }

    /**
     * Name property setter
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Name property getter
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Value property setter
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Value property getter
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
}

?>
