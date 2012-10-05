<?php
    require_once dirname(__FILE__) . "/serializer/XMLSerializer.php";
    require_once dirname(__FILE__) . "/serializer/JSONSerializer.php";

    class Serializer
    {
        private static $contentTypes;

        public static function serialize($data, $contentType)
        {
            self::$contentTypes = array(
                'application/json' => "JSONSerializer",
                'application/xml'  => "XMLSerializer",
                'text/xml'         => "XMLSerializer",
            );

            // add common content-types - set them to use the default content-type
            $defaultContentType = Configuration::get("content-type");
            $defaultSerializer = self::$contentTypes[$defaultContentType];

            if(isset($defaultSerializer))
            {
                if(!isset(self::$contentTypes["text/html"]))
                    self::$contentTypes["text/html"] = $defaultSerializer;

                if(!isset(self::$contentTypes["text/plain"]))
                    self::$contentTypes["text/plain"] = $defaultSerializer;
            }

            return self::parse($data, $contentType);
        }

        private static function parse($data, $contentType)
        {
            if(!is_array($data) && empty($data))
                return $data;

            $defaultContentType = Configuration::get("content-type");
            $serializer = self::$contentTypes[$contentType];
            if(!isset($serializer))
                $serializer = self::$contentTypes[$defaultContentType];

            if(!$serializer)
                throw new Exception("Cannot find serializer for default content type \"".$defaultContentType."\"");

            if(class_exists($serializer))
            {
                $result = call_user_func(array($serializer, "parse"), $data);
                if(!empty($result))
                    return $result;
            }
            else
                throw new Exception("Cannot find serializer type \"".$serializer."\"");

            return $data;
        }
    }