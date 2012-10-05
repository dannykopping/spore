<?php

	date_default_timezone_set("UTC");

    /**
     * The path to the config folder
     *
     * @see /projectroot/config
     */
    Configuration::set("config", dirname(__FILE__));

    /**
     * The path to the Slim library
     */
    Configuration::set("slim", dirname(__FILE__)."/../slim/Slim");

    /**
     * The path to the Slim Plugins library
     */
    Configuration::set("plugins", dirname(__FILE__)."/../slim-plugins/Plugins");

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
    Configuration::set("services", dirname(__FILE__)."/../../../src_php/service");


	/**
	 * Database connection
	 */
	Configuration::set("DB_ENGINE", "mysql");
	Configuration::set("DB_USERNAME", "root");
	Configuration::set("DB_PASSWORD", "mac150189");
	Configuration::set("DB_HOST", "localhost");
	Configuration::set("DB_PORT", "3306");
	Configuration::set("DB_SCHEMA", "triptrackr");

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