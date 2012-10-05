<?php
	namespace ReST\Data;

    class Deserializer extends DeserializerMiddleware
    {
        public function __construct($app, $settings = array())
        {
            parent::__construct($app, $settings);

            $this->contentTypes = array_merge(array(
                                                   'application/json' => "JSONDeserializer",
                                                   'application/xml'  => "XMLDeserializer",
                                                   'text/xml'         => "XMLDeserializer",
                                                   'text/csv'         => "CSVDeserializer"
                                              ), $settings);
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
            if(empty($data))
                return $data;

            $defaultContentType = Configuration::get("content-type");
            $deserializer = $this->contentTypes[$contentType];
            if(!isset($deserializer))
                $deserializer = $this->contentTypes[$defaultContentType];

            if(!$deserializer)
                throw new Exception("Cannot find deserializer for default content type \"" . $defaultContentType . "\"");

            if(class_exists($deserializer))
            {
                $result = call_user_func(array($deserializer, "parse"), $data);
                if(!empty($result))
                    return $result;

				throw new Exception("An error occurred while attempting to deserialize data");
            }
            else
                throw new Exception("Cannot find deserializer type \"" . $deserializer . "\"");

            return $data;
        }
    }