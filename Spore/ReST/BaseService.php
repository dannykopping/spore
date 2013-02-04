<?php
namespace Spore\ReST;

use Spore\ReST\Model\Request;
use Spore\ReST\AutoRoute\Route;
use Spore\ReST\Model\Response;
use Spore\Spore;

/**
 *  This abstract class provides basic functionality and access to
 *  any service class that is derived from it
 */
abstract class BaseService
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var
     */
    protected $request;

    /**
     * @var
     */
    protected $response;

    /**
     * @var
     */
    protected $autoroute;

    /**
     * @param \Spore\Spore $app
     */
    public function __construct(Spore $app)
    {
        $this->setApp($app);

        if (!$app) {
            return;
        }

        $app->hook("spore.autoroute.before", array($this, "initialize"));
    }

    /**
     * @param $args
     */
    public function initialize($args)
    {
        $this->setRequest(isset($args["request"]) ? $args["request"] : null);
        $this->setResponse(isset($args["response"]) ? $args["response"] : null);
        $this->setAutoroute(isset($args["autoroute"]) ? $args["autoroute"] : null);
    }

    /**
     * @param \Spore\Spore $app
     */
    public function setApp($app)
    {
        $this->app = $app;
    }

    /**
     * @return Spore
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param Model\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Model\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param AutoRoute\Route $autoroute
     */
    public function setAutoroute($autoroute)
    {
        $this->autoroute = $autoroute;
    }

    /**
     * @return array
     */
    public function getAutoroute()
    {
        return $this->autoroute;
    }
}
