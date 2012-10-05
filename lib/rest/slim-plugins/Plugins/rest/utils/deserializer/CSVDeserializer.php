<?php
    require_once dirname(__FILE__) . "/BaseDeserializer.php";

    class CSVDeserializer extends BaseDeserializer
    {
        /**
         * Parse CSV
         *
         * This method parses CSV content into a numeric array
         * containing an array of data for each CSV line.
         *
         * @param   string $data
         * @return  array
         */
        public static function parse($data)
        {
            $temp = fopen('php://memory', 'rw');
            fwrite($temp, $data);
            fseek($temp, 0);
            $res = array();
            while(($data = fgetcsv($temp)) !== false)
            {
                $res[] = $data;
            }
            fclose($temp);
            return $res;
        }
    }
