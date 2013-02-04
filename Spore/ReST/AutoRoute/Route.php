<?php
namespace Spore\ReST\AutoRoute;

use Spore\ReST\AutoRoute\Util\RouteDescriptor;
use Exception;

/**
 *    A class that defines what an "auto-route" is
 */
class Route
{
    /**
     * @var    string        The @name annotation value
     */
    private $_name;

    /**
     * @var    string        The @url annotation value
     */
    private $_uri;

    /**
     * @var    array        An array of arguments passed to the auto-route
     */
    private $_arguments;

    /**
     * @var    array        The @auth annotation value
     */
    private $_authorizedUsers;

    /**
     * @var    string        The @verbs annotation value
     */
    private $_methods;

    /**
     * @var    string        The @template annotation value
     */
    private $_template;

    /**
     * @var    string        The @render annotation value
     */
    private $_render;

    /**
     * @var    string        The @condition annotation values
     */
    private $_conditions;

    /**
     * @var    callable    The callback function related to this auto-route
     */
    private $_callback;

    /**
     * @var Util\RouteDescriptor        The related RouteDescriptor
     */
    private $_descriptor;

    /**
     * Constructor
     *
     * @param Util\RouteDescriptor $descriptor
     */
    public function __construct(RouteDescriptor $descriptor)
    {
        $this->_descriptor = $descriptor;
    }

    /**
     * Converts this object to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUri() . "\n" .
                print_r($this->getArguments(), true) . "\n" .
                print_r($this->getMethods(), true) . "\n";
    }

    /**
     * Arguments property setter
     *
     * @param $arguments
     */
    public function setArguments($arguments)
    {
        $this->_arguments = $arguments;
    }

    /**
     * Arguments property getter
     *
     * @return mixed
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Methods property setter
     *
     * @param $methods
     */
    public function setMethods($methods)
    {
        $this->_methods = $methods;
    }

    /**
     * Methods property getter
     *
     * @return mixed
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * URI property setter
     *
     * @param $uri
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

    /**
     * URI property getter
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Callback property setter
     *
     * @param $callback
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    /**
     * Callback property getter
     *
     * @return mixed
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * Authorized users property setter
     *
     * @param $authorizedUsers
     */
    public function setAuthorizedUsers($authorizedUsers)
    {
        $this->_authorizedUsers = $authorizedUsers;
    }

    /**
     * Authorized users property getter
     *
     * @return mixed
     */
    public function getAuthorizedUsers()
    {
        return $this->_authorizedUsers;
    }

    /**
     * RouteDescriptor property getter
     *
     * @return Util\RouteDescriptor
     */
    public function getDescriptor()
    {
        return $this->_descriptor;
    }

    /**
     * Template property setter
     *
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Template property getter
     *
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Render mode property setter
     *
     * @param $render
     *
     * @throws \Exception
     */
    public function setRender($render)
    {
        $normalizedValue = strtolower($render);
        $acceptable = array("always", "nonxhr", "nonajax", "non-xhr", "non-ajax", "never");

        if (!in_array($normalizedValue, $acceptable)) {
            throw new Exception("$render is not a valid option for the @" . AutoRouter::RENDER . " annotation");
        }

        $this->_render = $normalizedValue;
    }

    /**
     * Render mode property getter
     *
     * @return mixed
     */
    public function getRender()
    {
        return $this->_render;
    }

    /**
     * Name property setter
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->_name = trim($name);
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
     * Conditions property setter
     *
     * @param string $conditions
     */
    public function setConditions($conditions)
    {
        $this->_conditions = $conditions;
    }

    /**
     * Conditions property getter
     *
     * @return string
     */
    public function getConditions()
    {
        return $this->_conditions;
    }
}

?>
