<?php
namespace Spore\ReST;

use Slim\Slim;
use Spore\ReST\Data\Deserializer;
use ReflectionClass;
use Spore\ReST\AutoRoute\AutoRouter;
use Spore\ReST\Data\Middleware\DeserializerMiddleware;
use Spore\ReST\Model\Response;
use Spore\ReST\Model\Request;
use Slim\Route;
use Spore\ReST\Data\Serializer;
use Spore\Auth\AccessController;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Exception;

/**
 * Class to control the operation and configuration of Slim Framework
 */
class Controller
{
    /**
     * @var    Controller                       Singleton instance
     */
    private static $instance;

    /**
     * @var     Spore                             Reference to Spore application
     */
    private $_slimInstance;

    /**
     * @var    callable                            Authorization callback function
     */
    private $_authorizationCallback;

    /**
     * @var     AutoRouter
     */
    private $autorouter;

    /**
     * Initialize the Controller
     *
     * @throws Exception
     */
    private function initialize()
    {
        $app = $this->getApp();

        if (empty($app)) {
            throw new Exception("Controller could not be initialized with an empty Slim instance");
        }

        // add Slim middleware to deserialize HTTP request body data
        $this->addRequestBodyDeserializer();
    }

    /**
     * @static
     * @return Controller
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }

        return self::$instance;
    }

    /**
     * @param Spore $slimInstance
     */
    public function setApp(\Spore\Spore $slimInstance)
    {
        $this->_slimInstance = $slimInstance;

        $this->initialize();
    }

    /**
     * @return Spore
     */
    public function getApp()
    {
        return $this->_slimInstance;
    }

    /**
     *    Add deserializer middleware
     */
    private function addRequestBodyDeserializer()
    {
        $app = $this->getApp();

        $deserializer = new Deserializer($app);
        $app->add($deserializer);
    }

    /**
     * Recursively scans the "services" config directory, requiring each PHP class it finds, and returning an
     * array of classes to be added to the auto-route list
     *
     * @return array
     */
    public function findServices()
    {
        $app = $this->getApp();

        $services = $app->config("services");
        $classes = array();

        foreach ($services as $service) {
            if (is_object($service) && !is_string($service)) {
                $classes[] = $service;
            }
        }

        return $classes;
    }

    /**
     * Add auto-routing middleware
     *
     * @param array $classes
     */
    public function addAutoRouting(array $classes)
    {
        $app = $this->getApp();
        $this->autorouter = new AutoRouter($app, $classes);
    }

    /**
     * Set the authorization callback function
     *
     * @param $authorizationCallback
     *
     * @throws \Exception
     */
    public function setAuthCallback($authorizationCallback)
    {
        $app = $this->getApp();
        $access = new AccessController($app);
        $access->authorizationCallback($authorizationCallback);

        if (!is_callable($authorizationCallback)) {
            $this->_authorizationCallback = null;
            AccessController::authorizationCallback(null);

            throw new Exception("Function used for authCallback is not callable.");
        }

        $this->_authorizationCallback = $authorizationCallback;

        AccessController::authorizationCallback($this->getAuthCallback());
    }

    /**
     * Get the authorization callback function
     *
     * @return mixed
     */
    public function getAuthCallback()
    {
        return $this->_authorizationCallback;
    }
}
