<?php
	namespace Spore\ReST\Data\Serializer;

    /**
	 *	The Base serialization class
	 */
	abstract class Base
    {
		/**
		 * Parse the given data into an encoding
		 *
		 * @param $data
		 */
		public static function parse($data){}
    }
