<?php
	/**
	 * Modification of configuration values
	 *
	 * @author Danny Kopping
	 */
	class AerialConfiguration
	{
		private static $_instance;
	
		public function __construct()
		{
			if(isset(self::$_instance))
				trigger_error("You must not call the constructor directly! This class is a Singleton");	
		}
	
		public static function getInstance()
		{
			if(!isset(self::$_instance))
			{
				self::$_instance = new self();
				self::init();
			}
	
			return self::$_instance;
		}
	
		public function getConfigurationOptions()
		{
			$constants = get_defined_constants(true);
			return $constants['user'];
		}
		
		private function hasTables()
		{
			$conn = Doctrine_Manager::getInstance()->getConnection("doctrine");
			$query = $conn->execute("SHOW TABLES");
			$results = $query->fetchAll();
			
			return count($results) != 0;
		}

        /**
         * @route               /codegen/get-yaml
         * @routeMethods        GET
         * @contentType         txt
         *
         */
        public function getYAMLFromDatabase()
        {
//            return Aerial_Core::generateYamlFromDb(WWW_PATH);
            $modelsPath = Configuration::get("PHP_MODELS");

            $options = array(
            				"baseClassName" => "Aerial_Record",
            				"baseClassesDirectory" => "base"
                );

            $dataToWrite = Aerial_Core::generateModelsFromDb($modelsPath, array("doctrine"), $options);
            return $dataToWrite;
        }

		public function getDefinitionsFromYAML($packageName="org.aerialframework.vo")
		{
			$modelsPath = ConfigXml::getInstance()->modelsPath;

			$options = array(
				"baseClassName" => "Aerial_Record",
				"baseClassesDirectory" => "base",
				"actionscriptPackageName" => $packageName
			);

			$emulated = Aerial_Core::generateEmulatedModelsFromYaml(CONFIG_PATH. DIRECTORY_SEPARATOR . 'schema.yml', $modelsPath, $options);
			foreach($emulated as $className => &$definition)
			{
				foreach($definition["fields"] as &$field)
				{
					$field["type"] = $this->getAS3Type($field["type"], $field["unsigned"]);
					unset($field["unsigned"]);
				}
			}

			return $emulated;
		}
	
		public function generate($fromYAML=true, $regenDB=false, $packageName="org.aerialframework.vo")
		{					
			if($regenDB)
			{
				Doctrine_Core::dropDatabases();
				Doctrine_Core::createDatabases();
			}

			$options = array(
				"baseClassName" => "Aerial_Record",
				"baseClassesDirectory" => "base",
				"actionscriptPackageName" => $packageName
			);

			$modelsPath = ConfigXml::getInstance()->modelsPath;

			if($fromYAML)
				$dataToWrite = Aerial_Core::generateModelsFromYaml(CONFIG_PATH . DIRECTORY_SEPARATOR .'schema.yml', $modelsPath, $options);
			else
			{
				if(!file_exists($modelsPath))					// if the folder does not exist, create it to avoid errors!
					@mkdir($modelsPath, 0766, true);

                // if file STILL does not exist, throw an error
                if(!file_exists($modelsPath))
                    trigger_error("Cannot create folder: ".$modelsPath);

				$dataToWrite = Aerial_Core::generateModelsFromDb($modelsPath, array("doctrine"), $options);
			}

            return $dataToWrite;
		}

        public function regenerateFromModels($regenDB=false)
        {
			if($regenDB)
			{
				Doctrine_Core::dropDatabases();
				Doctrine_Core::createDatabases();
			}
            
			$models = Doctrine_Core::getLoadedModels();
            if(count($models) == 0)
            {
                trigger_error("No models found.");
                return;
            }
            else
            {
                Doctrine_Core::createTablesFromModels();
                return count($models);;
            }
        }

		public function getModels()
		{
			$models = Doctrine_Core::getLoadedModels();
			return $models;
		}

		/**
		 * Get details about each models' properties, type, etc
		 *
		 * @param  $models
		 * @return void
		 */
		public function getModelDefinitions($models=null)
		{
			if(!$models)
				$models = $this->getModels();

			if(count($models) == 0)
				return;

			$details = array();
			foreach($models as $model)
			{
				//$details[$model] = array("HELLO!");
				//return $details;

				$instance = new $model;
				$table = $instance->table;

				$details[$model] = array();
				foreach($table->getColumnNames() as $column)
				{
					$definition = $table->getColumnDefinition($column);					
					$as3Type = $this->getAS3Type($definition["type"], (bool) $definition["unsigned"]);

					$details[$model][] = array("name" => $table->getFieldName($column),
												//"type" => $definition["type"],
												"type" => $as3Type,
												"length" => $definition["length"]);
				}
				
				$relations = $table->getRelations();
				foreach($relations as $relation)
				{
					$details[$model][] = array("relation" => true,
												"name" => $relation->getAlias(),
												"type" => $relation->getClass(),
												"many" => (bool) $relation->getType());
				}

				$instance = null;
			}

			return $details;
		}

		public function getAS3Type($type, $unsigned)
		{
			$as3type = "";
			switch ($type)
			{
                case 'integer':
                	$as3type = $unsigned ? "uint" : "int";
                	break;
                case 'decimal':
                case 'float':
				case 'double':
                	$as3type = "Number";
                	break;
                case 'set':
                case 'array':
                	$as3type = "Array";
                	break;
                case 'boolean':
                	$as3type = "Boolean";
                	break;
                case 'blob':
	                $as3type = "ByteArray";
			        break;
                case 'object':
                	$as3type = "Object";
                	break;
                case 'time':
                case 'timestamp':
                case 'date':
				case 'datetime':
					$as3type = "Date";
					break;
                case 'enum':
                case 'gzip':
                case 'string':
                case 'clob':
                	$as3type = "String";
					break;
                default:
                	$as3type = $type;
                	break;
			}

			return $as3type;
		}
	}
?>