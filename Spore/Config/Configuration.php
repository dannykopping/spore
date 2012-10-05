<?php
	namespace Spore\Config;

	date_default_timezone_set("UTC");

    /**
     * The path to the config folder
     *
     * @see /projectroot/config
     */
    Configuration::set("config", dirname(__FILE__));

    /**
     * Debug mode - show debugging messages and data if TRUE
     */
    Configuration::set("debug", true);

    /**
     * The default encoding of the response data
     */
    Configuration::set("content-type", "application/xml");

    /**
     * GZIP compression of output data
     */
    Configuration::set("gzip", true);

    /**
     * The path to services
     */
    Configuration::set("services", __DIR__."/../Services");

    /**
     * The namespace of the services (optional)
     */
    Configuration::set("services-ns", "Spore\\Services");

	Configuration::load();

    class Configuration
    {
        private static $definitions = array();

        // do not allow instantiation
        final private function __construct(){}

        public static function set($name, $value)
        {
            self::$definitions[$name] = $value;
        }

        public static function get($name)
        {
            return isset(self::$definitions[$name]) ? self::$definitions[$name] : null;
        }

        public static function load()
        {
        }
    }