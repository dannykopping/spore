<?php

    if(!function_exists("is_undefined"))
    {
        function is_undefined($obj)
        {
            return is_object($obj) ? get_class($obj) == "Amfphp_Core_Amf_Types_Undefined" : false;
        }
    }