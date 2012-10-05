<?php
	namespace ReST\Data\Serializer;

	use ReST\Data\Serializer\Base;

    class JSONSerializer extends Base
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
