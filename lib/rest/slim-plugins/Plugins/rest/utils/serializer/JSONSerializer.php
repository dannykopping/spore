<?php
    require_once dirname(__FILE__) . "/BaseSerializer.php";

    class JSONSerializer extends BaseSerializer
    {
        public static function parse($data)
        {
            if(function_exists("json_encode"))
            {
                return json_encode($data);
            }
            else
            {
                return $data;
            }
        }
    }
