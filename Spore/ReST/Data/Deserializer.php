<?php
	namespace Spore\ReST\Data;

    use Spore\ReST\Data\Middleware\DeserializerMiddleware;
	use Exception;

	/**
	 *	This class extends the DeserializerMiddleware class
	 * 	and implements data deserialization
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

            $this->contentTypes = array_merge(array(
                                                   'application/json' => "\\Spore\\ReST\\Data\\Deserializer\\JSONDeserializer",
                                                   'application/xml'  => "\\Spore\\ReST\\Data\\Deserializer\\XMLDeserializer",
                                                   'text/xml'         => "\\Spore\\ReST\\Data\\Deserializer\\XMLDeserializer",
                                                   'text/csv'         => "\\Spore\\ReST\\Data\\Deserializer\\CSVDeserializer"
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

            $defaultContentType = $this->getApplication()->config("content-type");
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