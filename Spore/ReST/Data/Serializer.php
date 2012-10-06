<?php
	namespace Spore\ReST\Data;

	use Slim\Slim;
	use Spore\Spore;
	use Exception;

	use Spore\ReST\Data\Serializer\JSONSerializer;
	use Spore\ReST\Data\Serializer\XMLSerializer;

	class Serializer
    {
        private static $contentTypes;

        public static function serialize($data, $contentType)
        {
			$app = Spore::getInstance();

            self::$contentTypes = array(
                'application/json' => "\\Spore\\ReST\\Data\\Serializer\\JSONSerializer",
                'application/xml'  => "\\Spore\\ReST\\Data\\Serializer\\XMLSerializer",
                'text/xml'         => "\\Spore\\ReST\\Data\\Serializer\\XMLSerializer",
            );

            // add common content-types - set them to use the default content-type
            $defaultContentType = $app->config("content-type");
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
			$app = Spore::getInstance();

            if(!is_array($data) && empty($data))
                return $data;

            $defaultContentType = $app->config("content-type");
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

		public static function getSerializedData(Slim $app, $data)
		{
			$env                    = $app->environment();
			$acceptableContentTypes = explode(";", $env["ACCEPT"]);

			$contentType = "";

			if(count($acceptableContentTypes) > 1 || empty($acceptableContentTypes))
				$contentType = $app->config("content-type");
			else
				$contentType = $acceptableContentTypes[0];

			// don't allow */* as the content-type, rather favour the default content-type
			if($contentType == "*/*")
				$contentType = $app->config("content-type");

			$app->contentType($contentType);

			if(is_a($data, "Aerial_Record") || is_a($data, "Doctrine_Collection"))
				$data = $data->toArray();

			$data = self::serialize($data, $contentType);

			return $data;
		}
    }