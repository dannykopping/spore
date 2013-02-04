<?php
namespace Spore\ReST\Model;

/**
 *    This class is a convenience class which provides access to
 *     data passed in via the various accepted HTTP practices
 */
class Request
{
    /**
     * @var stdClass|array        The deserialized request body
     */
    public $data = null;

    /**
     * @var array                The parsed HTTP query string parameters
     */
    public $queryParams = array();

    /**
     * @var array                The Slim URI parameters
     */
    public $params = array();

    /**
     * @var array                The uploaded files information
     */
    public $files = array();

    /**
     * @var \Slim\Http\Request    A reference to the Slim Request
     */
    private $request;

    /**
     * Get the Slim Request object
     *
     * @return \Slim\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Set the Slim Request object
     *
     * @param $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }
}
