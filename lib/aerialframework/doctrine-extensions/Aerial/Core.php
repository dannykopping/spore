<?php

    class Aerial_Core extends Doctrine_Core
    {
        //Hydrators
        const HYDRATE_AMF_ARRAY = "Aerial_Hydrator_ArrayDriver";
        const HYDRATE_AMF_COLLECTION = "Aerial_Hydrator_CollectionDriver";

        /**
         * if set, to true, the Aerial YAML code generation will maintain column naming conventions
         * Doctrine, by default, lower-cases all column names when exporting YAML from a database schema
         */
        const ATTR_YAML_MAINTAIN_COLUMN_NAMES           = 200;

        private static $_path;

        /**
         * simple autoload function
         * returns true if the class was loaded, otherwise false
         *
         * @param string $className
         * @return boolean
         */
        public static function autoload($className)
        {
            if(strpos($className, 'sfYaml') === 0)
            {
                require dirname(__FILE__) . '/Parser/sfYaml/' . $className . '.php';

                return true;
            }

            if(0 !== stripos($className, 'Aerial_') || class_exists($className, false) || interface_exists($className, false))
            {
                return false;
            }

            $class = self::getPath() . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

            if(file_exists($class))
            {
                require $class;

                return true;
            }

            return false;
        }

        /**
         * Get the root path to Doctrine
         *
         * @return string
         */
        public static function getPath()
        {
            if(!self::$_path)
            {
                self::$_path = realpath(dirname(__FILE__) . '/..');
            }

            return self::$_path;
        }

        /**
         * Build pre-YAML definitions from database
         *
         * @static
         * @param array $options
         * @return bool|void
         * @throws Doctrine_Exception
         */
        public static function generateDefinitionsFromDb(array $options = array())
        {
            $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_doctrine_models';

            $options['generateBaseClasses'] = isset($options['generateBaseClasses']) ? $options['generateBaseClasses'] : false;

            // generate temporary models based on DB
            $result = Doctrine_Core::generateModelsFromDb($directory, array(), $options);

            if(empty($result) && !is_dir($directory))
                throw new Doctrine_Exception('No models generated from your databases');

            // build definition of the schema, from the models
            $export = new Aerial_Export_Schema();
            $schema = $export->buildSchema($directory, array(), Doctrine_Core::MODEL_LOADING_AGGRESSIVE);

            // remove the temporary models
            Doctrine_Lib::removeDirectories($directory);

            // return definition
            return $schema;
        }

        public static function generateEmulatedModelsFromYaml($yamlPath, $directory, $options = array())
        {
            $import = new Aerial_Import_Schema();
            $import->setOptions($options);

            return $import->importEmulatedSchema($yamlPath, 'yml', $directory);
        }

        public static function generateModelsFromYaml($yamlPath, $directory, $options = array())
        {
            $import = new Aerial_Import_Schema();
            $import->setOptions($options);

            return $import->importSchema($yamlPath, 'yml', $directory);
        }
    }