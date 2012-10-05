<?php
    require_once dirname(__FILE__) . "/BaseDeserializer.php";

    class JSONDeserializer extends BaseDeserializer
    {
        /**
         * Parse JSON
         *
         * This method converts the raw JSON input
         * into an associative array.
         *
         * @param   string $data
         * @return  array|string
         */
        public static function parse($data)
        {
            if(function_exists("json_decode"))
            {
                return json_decode($data);
            }
            else
            {
                return $data;
            }
        }
    }
