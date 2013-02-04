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
        $this->contentTypes = $this->getApplication()->config("deserializers");

        if (empty($data)) {
            return $data;
        }

        $defaultContentType = $this->getApplication()->config("content-type");
        $deserializer = isset($this->contentTypes[$contentType]) ? $this->contentTypes[$contentType] : null;

        if (empty($deserializer) && empty($contentType)) {
            $deserializer = $this->contentTypes[$defaultContentType];
        }

        if (empty($deserializer) || !class_exists($deserializer)) {
            throw new Exception("Cannot find deserializer for content type \"" . $contentType . "\"");
        }

        $result = call_user_func(array($deserializer, "parse"), $data);
        if (!empty($result)) {
            return $result;
        }

        throw new Exception("An error occurred while attempting to deserialize data");
    }
}
