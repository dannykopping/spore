<?php
namespace Spore\ReST\Data;

use Slim\Slim;
use Spore\Spore;
use Exception;

use Spore\ReST\Data\Serializer\JSONSerializer;
use Spore\ReST\Data\Serializer\XMLSerializer;

/**
 *    This class implements data serialization from PHP primitive objects into
 *    a predefined content-type
 */
class Serializer
{
    /**
     * @var    array            A map which defines which serializers work with which content-types
     */
    private static $contentTypes;

    /**
     * Determine the appropriate serializer and pass the work
     * on to the `parse` function
     *
     * @param $app
     * @param $data
     * @param $contentType
     *
     * @return array|mixed
     */
    public static function serialize(Slim $app, $data, $contentType)
    {
        self::$contentTypes = $app->config("serializers");

        // add common content-types - set them to use the default content-type
        $defaultContentType = $app->config("content-type");
        $defaultSerializer = self::$contentTypes[$defaultContentType];

        if (isset($defaultSerializer)) {
            if (!isset(self::$contentTypes["text/html"])) {
                self::$contentTypes["text/html"] = $defaultSerializer;
            }

            if (!isset(self::$contentTypes["text/plain"])) {
                self::$contentTypes["text/plain"] = $defaultSerializer;
            }
        }

        return self::parse($app, $data, $contentType);
    }

    /**
     * Serialize the data based on the provided content-type
     *
     * @param $app
     * @param $data
     * @param $contentType
     *
     * @throws \Exception
     * @return array|mixed
     */
    private static function parse($app, $data, $contentType)
    {
        self::$contentTypes = $app->config("serializers");

        if (!is_array($data) && empty($data)) {
            return $data;
        }

        $defaultContentType = $app->config("content-type");
        $serializer = isset(self::$contentTypes[$contentType]) ? self::$contentTypes[$contentType] : null;

        if (empty($serializer) && empty($contentType)) {
            $serializer = self::$contentTypes[$defaultContentType];
        }

        // Assign a reference to the Spore instance
        call_user_func(array($serializer, "setApp"), $app);

        if (empty($serializer) || !class_exists($serializer)) {
            throw new Exception("Cannot find serializer for content type \"" . $contentType . "\"");
        }

        $result = call_user_func(array($serializer, "parse"), $data);
        if (!empty($result)) {
            return $result;
        }

        throw new Exception("An error occurred while attempting to serialize data");
    }

    /**
     * Serialize the data based on the provided "Accept" header if it exists,
     * otherwise revert to the predefined default content-type
     *
     * @param \Slim\Slim $app
     * @param            $data
     *
     * @return array|mixed
     */
    public static function getSerializedData(Slim $app, $data)
    {
        self::$contentTypes = $app->config("serializers");

        $env = $app->environment();
        $acceptableContentTypes = explode(";", $env["ACCEPT"]);

        $contentType = "";

        if (count($acceptableContentTypes) > 1 || empty($acceptableContentTypes)) {
            $contentType = $app->config("content-type");
        } else {
            $contentType = $acceptableContentTypes[0];
        }

        // don't allow */* as the content-type, rather favour the default content-type
        if ($contentType == "*/*") {
            $contentType = $app->config("content-type");
        }

        $app->contentType($contentType);

        $data = self::serialize($app, $data, $contentType);

        return $data;
    }
}
