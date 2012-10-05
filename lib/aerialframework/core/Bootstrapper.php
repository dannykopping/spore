<?php

	set_include_path(__DIR__."/../../");

    require_once "doctrine/Doctrine.php";
    require_once "aerialframework/doctrine-extensions/Aerial.php";
    require_once "aerialframework/utils/ModelMapper.php";
    require_once "aerialframework/utils/Date.php";
    require_once "aerialframework/utils/firephp/fb.php";

    require_once "aerialframework/encryption/Encrypted.php";
    require_once "aerialframework/encryption/Encryption.php";
    require_once "aerialframework/encryption/rc4crypt.php";

    require_once "aerialframework/exceptions/Aerial_Encryption_Exception.php";
    require_once "aerialframework/exceptions/Aerial_Exception.php";

    class Bootstrapper
    {
        public $conn;
        public $manager;

        private static $instance;

        private function __construct()
        {
            spl_autoload_register(array('Doctrine', 'autoload'));
            spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
            spl_autoload_register(array('Aerial', 'autoload'));

            $this->manager = Doctrine_Manager::getInstance();

            $this->manager->registerHydrator(Aerial_Core::HYDRATE_AMF_COLLECTION, Aerial_Core::HYDRATE_AMF_COLLECTION);
            $this->manager->registerHydrator(Aerial_Core::HYDRATE_AMF_ARRAY, Aerial_Core::HYDRATE_AMF_ARRAY);

            $this->manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
            $this->manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
            $this->manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);

            $this->manager->setAttribute(Doctrine_Core::ATTR_TABLE_CLASS, "Aerial_Table");

            $connectionString =
                Configuration::get("DB_ENGINE") . "://" .
                    Configuration::get("DB_USERNAME") . ":" .
                    Configuration::get("DB_PASSWORD") . "@" .
                    Configuration::get("DB_HOST") . ":" .
                    Configuration::get("DB_PORT") . "/" .
                    Configuration::get("DB_SCHEMA");

            try
            {
                $this->conn = Doctrine_Manager::connection($connectionString, "doctrine");
            }
            catch(Exception $e)
            {
                throw $e;
            }

            if(realpath(Configuration::get("PHP_MODELS")))
                Aerial_Core::loadModels(Configuration::get("PHP_MODELS"));
        }

        public static function setCredentials($username, $password)
        {
            $credentials = new stdClass();
            $credentials->username = $username;
            $credentials->password = $password;

            session_start();
            $_SESSION["credentials"] = $credentials;
        }

        public static function getInstance()
        {
            if(!isset(self::$instance))
            {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            return self::$instance;
        }
    }

?>