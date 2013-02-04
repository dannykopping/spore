<?php
namespace Spore\ReST\Model;

/**
 *    This class is a convenience class which provides access to
 *     the various features of HTTP responses exposed by Slim
 */
class Response
{
    /**
     * @var    int                        The HTTP status code
     */
    public $status;

    /**
     * @var    array                    The HTTP headers
     */
    public $headers;

    /**
     * @var \Slim\Http\Response        The Slim Response object
     */
    private $response;

    /**
     * Get the Slim Response object
     *
     * @return \Slim\Http\Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Set the Slim Response object
     *
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
