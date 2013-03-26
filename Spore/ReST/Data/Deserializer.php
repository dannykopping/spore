<?php
namespace Spore\ReST\Data;

use Spore\ReST\Data\Middleware\DeserializerMiddleware;
use Exception;

/**
 *    This class extends the DeserializerMiddleware class
 *     and implements data deserialization
 */
class Deserializer extends DeserializerMiddleware
{
    /**
     * Constructor
     *
     * @param \Slim\Slim $app
     * @param array      $settings
     */
    public function __construct($app, $settings = array())
    {
        parent::__construct($app, $settings);
    }


    /**
     * Parse input
     *
     * This method will attempt to parse the request body
     * based on its content type if available.
     *
     * @param   string $data
     * @param   string $contentType
     *
     * @throws Exception
     * @return  mixed
     */
    protected function parse($data, $contentType)
    {
        $app = $this->getApplication();
        $env = $app->environment();

        $this->contentTypes = $app->config("deserializers");

        if (empty($data)) {
            return $data;
        }

        $defaultContentType = $app->config("content-type");
        $deserializer = isset($this->contentTypes[$contentType]) ? $this->contentTypes[$contentType] : null;

        if (empty($deserializer) && empty($contentType)) {
            $deserializer = $this->contentTypes[$defaultContentType];
        }

        if (empty($deserializer) || !class_exists($deserializer)) {
            throw new Exception("Cannot find deserializer for content type \"" . $contentType . "\"");
        }

        $message = "An error occurred while attempting to deserialize data";

        try {
            $result = call_user_func(array($deserializer, "parse"), $data);
            if (!empty($result)) {
                return $result;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    
        $resp = $app->response();

        if (Serializer::isValidContentType($app)) {
            $resp->status($app->config('errors.parser-error'));
            $resp->body(Serializer::getSerializedData(
                $this->app, 
                array(
                    "error" => array(
                        "message" => $message)
                    )
                )
            );
        } else {
            $resp->status($app->config("errors.invalid-accept-type"));
            $resp->body("Cannot find serializer for content type \"" . $env['ACCEPT'] . "\"");
        }
    }
}
