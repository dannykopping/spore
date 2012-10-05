<?php
/**
 * Created by IntelliJ IDEA.
 * User: danny
 * Date: 2011/01/19
 * Time: 2:14 AM
 */
 
class Aerial_Import_Builder extends Doctrine_Import_Builder
{
	public $actionscriptPackageName;

    /**
     * buildDefinition
     *
     * @param array $definition
     * @return string
     */
    public function buildDefinition(array $definition)
    {
        if ( ! isset($definition['className'])) {
            throw new Doctrine_Import_Builder_Exception('Missing class name.');
        }
        $abstract = isset($definition['abstract']) && $definition['abstract'] === true ? 'abstract ':null;
        $className = $definition['className'];
        $extends = isset($definition['inheritance']['extends']) ? $definition['inheritance']['extends']:$this->_baseClassName;

        if ( ! (isset($definition['no_definition']) && $definition['no_definition'] === true)) {
            $tableDefinitionCode = $this->buildTableDefinition($definition);
            $setUpCode = $this->buildSetUp($definition);
	        $mapping = $this->buildClassMapping($definition);
        } else {
            $tableDefinitionCode = null;
            $setUpCode = null;
	        $mapping = null;
        }

        if ($tableDefinitionCode && $setUpCode) {
            $setUpCode = PHP_EOL . $setUpCode;
	        if($mapping)
				$setUpCode .= PHP_EOL . $mapping;
        }


        $docs = PHP_EOL . $this->buildPhpDocs($definition);

        $content = sprintf(self::$_tpl, $docs, $abstract,
                                       $className,
                                       $extends,
                                       $tableDefinitionCode,
                                       $setUpCode);

        return $content;
    }

	/**
	 * Build php code for AMFPHP class mapping
	 *
	 * @param array $mapping
	 * @return string $build
	 */
	public function buildClassMapping($definition)
	{
		$voPath = $this->actionscriptPackageName.".".$definition["topLevelClassName"];

        $build = "\$this->mapValue('_explicitType', '$voPath');" . PHP_EOL;
		return PHP_EOL.'    public function construct()' . PHP_EOL . '    {' . PHP_EOL . '        ' . $build . '    }';
	}

    /**
     * Gets the definitions of (as yet) uncreated Doctrine models
     *
     * @throws Doctrine_Import_Builder_Exception
     * @param array $definition
     * @return array
     */
    public function buildEmulatedRecord(array $definition)
    {
        if ( ! isset($definition['className'])) {
            throw new Doctrine_Import_Builder_Exception('Missing class name.');
        }

        $definition['topLevelClassName'] = $definition['className'];

        if ($this->generateBaseClasses())
        {
            $definition['is_package'] = (isset($definition['package']) && $definition['package']) ? true:false;

            if ($definition['is_package']) {
                $e = explode('.', trim($definition['package']));
                $definition['package_name'] = $e[0];

                $definition['package_path'] = ! empty($e) ? implode(DIRECTORY_SEPARATOR, $e):$definition['package_name'];
            }
            // Top level definition that extends from all the others
            $topLevel = $definition;
            unset($topLevel['tableName']);

            // If we have a package then we need to make this extend the package definition and not the base definition
            // The package definition will then extends the base definition
            $topLevel['inheritance']['extends'] = (isset($topLevel['package']) && $topLevel['package']) ? $this->_packagesPrefix . $topLevel['className']:$this->_baseClassPrefix . $topLevel['className'];
            $topLevel['no_definition'] = true;
            $topLevel['generate_once'] = true;
            $topLevel['is_main_class'] = true;
            unset($topLevel['connection']);

            // Package level definition that extends from the base definition
            if (isset($definition['package'])) {

                $packageLevel = $definition;
                $packageLevel['className'] = $topLevel['inheritance']['extends'];
                $packageLevel['inheritance']['extends'] = $this->_baseClassPrefix . $topLevel['className'];
                $packageLevel['no_definition'] = true;
                $packageLevel['abstract'] = true;
                $packageLevel['override_parent'] = true;
                $packageLevel['generate_once'] = true;
                $packageLevel['is_package_class'] = true;
                unset($packageLevel['connection']);

                $packageLevel['tableClassName'] = $packageLevel['className'] . 'Table';
                $packageLevel['inheritance']['tableExtends'] = isset($definition['inheritance']['extends']) ? $definition['inheritance']['extends'] . 'Table':$this->_baseTableClassName;

                $topLevel['tableClassName'] = $topLevel['topLevelClassName'] . 'Table';
                $topLevel['inheritance']['tableExtends'] = $packageLevel['className'] . 'Table';
            } else {
                $topLevel['tableClassName'] = $topLevel['className'] . 'Table';
                $topLevel['inheritance']['tableExtends'] = isset($definition['inheritance']['extends']) ? $definition['inheritance']['extends'] . 'Table':$this->_baseTableClassName;
            }

            $baseClass = $definition;
            $baseClass['className'] = $this->_baseClassPrefix . $baseClass['className'];
            $baseClass['abstract'] = true;
            $baseClass['override_parent'] = false;
            $baseClass['is_base_class'] = true;

            $definition["files"] = $this->buildRecord($definition);

	        return $this->getEmulatedDefinition($definition);
        }
	    else
		    return null;
    }

    /**
     * buildRecord
     *
     * @param array $options
     * @param array $columns
     * @param array $relations
     * @param array $indexes
     * @param array $attributes
     * @param array $templates
     * @param array $actAs
     * @return void=
     */
    public function buildRecord(array $definition)
    {
        if ( ! isset($definition['className'])) {
            throw new Doctrine_Import_Builder_Exception('Missing class name.');
        }

        $definition['topLevelClassName'] = $definition['className'];

        if ($this->generateBaseClasses()) {
            $definition['is_package'] = (isset($definition['package']) && $definition['package']) ? true:false;

            if ($definition['is_package']) {
                $e = explode('.', trim($definition['package']));
                $definition['package_name'] = $e[0];

                $definition['package_path'] = ! empty($e) ? implode(DIRECTORY_SEPARATOR, $e):$definition['package_name'];
            }
            // Top level definition that extends from all the others
            $topLevel = $definition;
            unset($topLevel['tableName']);

            // If we have a package then we need to make this extend the package definition and not the base definition
            // The package definition will then extends the base definition
            $topLevel['inheritance']['extends'] = (isset($topLevel['package']) && $topLevel['package']) ? $this->_packagesPrefix . $topLevel['className']:$this->_baseClassPrefix . $topLevel['className'];
            $topLevel['no_definition'] = true;
            $topLevel['generate_once'] = true;
            $topLevel['is_main_class'] = true;
            unset($topLevel['connection']);

            // Package level definition that extends from the base definition
            if (isset($definition['package'])) {

                $packageLevel = $definition;
                $packageLevel['className'] = $topLevel['inheritance']['extends'];
                $packageLevel['inheritance']['extends'] = $this->_baseClassPrefix . $topLevel['className'];
                $packageLevel['no_definition'] = true;
                $packageLevel['abstract'] = true;
                $packageLevel['override_parent'] = true;
                $packageLevel['generate_once'] = true;
                $packageLevel['is_package_class'] = true;
                unset($packageLevel['connection']);

                $packageLevel['tableClassName'] = $packageLevel['className'] . 'Table';
                $packageLevel['inheritance']['tableExtends'] = isset($definition['inheritance']['extends']) ? $definition['inheritance']['extends'] . 'Table':$this->_baseTableClassName;

                $topLevel['tableClassName'] = $topLevel['topLevelClassName'] . 'Table';
                $topLevel['inheritance']['tableExtends'] = $packageLevel['className'] . 'Table';
            } else {
                $topLevel['tableClassName'] = $topLevel['className'] . 'Table';
                $topLevel['inheritance']['tableExtends'] = isset($definition['inheritance']['extends']) ? $definition['inheritance']['extends'] . 'Table':$this->_baseTableClassName;
            }

            $baseClass = $definition;
            $baseClass['className'] = $this->_baseClassPrefix . $baseClass['className'];
            $baseClass['abstract'] = true;
            $baseClass['override_parent'] = false;
            $baseClass['is_base_class'] = true;

            $baseClassToWrite = $this->writeDefinition($baseClass);

            if ( ! empty($packageLevel)) {
                $packageLevelToWrite = $this->writeDefinition($packageLevel);
            }

            $topLevelToWrite = $this->writeDefinition($topLevel);
        } else {
            $definitionToWrite = $this->writeDefinition($definition);
        }

        return array("baseClass" => $baseClassToWrite,
                        "topLevelClass" => $topLevelToWrite);
    }

	private function getEmulatedDefinition($definition)
	{
		$fields = array();
        $files = $definition["files"];

        unset($definition["files"]);

		foreach ($definition['columns'] as $name => $column)
		{
			$name = isset($column['name']) ? $column['name']:$name;
			// extract column name & field name
			if (stripos($name, ' as '))
			{
				if (strpos($name, ' as')) {
					$parts = explode(' as ', $name);
				} else {
					$parts = explode(' AS ', $name);
				}

				if (count($parts) > 1) {
					$fieldName = $parts[1];
				} else {
					$fieldName = $parts[0];
				}

				$name = $parts[0];
			}
			else
			{
				$fieldName = $name;
				$name = $name;
			}

			$name = trim($name);
			$fieldName = trim($fieldName);

			$fields[] = array("name" => $fieldName, "type" => $column['type'],
			                    "unsigned" => $column["unsigned"] ? $column["unsigned"] : false,
								"relation" => false, "many" => "false",
								"length" => $column["length"] ? $column["length"] : null);
		}

		if (isset($definition['relations']) && ! empty($definition['relations']))
		{
			foreach ($definition['relations'] as $relation)
			{
				$type = $this->_classPrefix . $relation['class'];
				$fields[] = array("name" => $relation['alias'], "type" => $type, "unsigned" => false,
				                  "relation" => true, "many" => isset($relation['type']) && $relation['type'] == Doctrine_Relation::MANY);
			}
		}

		return array("fields" => $fields, "files" => $files);
	}
    
    /**
     * writeDefinition
     *
     * @param array $options
     * @param array $columns
     * @param array $relations
     * @param array $indexes
     * @param array $attributes
     * @param array $templates
     * @param array $actAs
     * @return void
     */
    public function writeDefinition(array $definition)
    {
        $originalClassName = $definition['className'];
        if ($prefix = $this->_classPrefix) {
            $definition['className'] = $prefix . $definition['className'];
            if (isset($definition['connectionClassName'])) {
                $definition['connectionClassName'] = $prefix . $definition['connectionClassName'];
            }
            $definition['topLevelClassName'] = $prefix . $definition['topLevelClassName'];
            if (isset($definition['inheritance']['extends'])) {
                $definition['inheritance']['extends'] = $prefix . $definition['inheritance']['extends'];
            }
        }

        $definitionCode = $this->buildDefinition($definition);

        if ($prefix) {
            $definitionCode = str_replace("this->hasOne('", "this->hasOne('$prefix", $definitionCode);
            $definitionCode = str_replace("this->hasMany('", "this->hasMany('$prefix", $definitionCode);
            $definitionCode = str_replace("'refClass' => '", "'refClass' => '$prefix", $definitionCode);
        }

        if ($this->_classPrefixFiles) {
            $fileName = $definition['className'] . $this->_suffix;
        } else {
            $fileName = $originalClassName . $this->_suffix;
        }

        if ($this->_pearStyle) {
            $fileName = str_replace('_', '/', $fileName);
        }

        $packagesPath = $this->_packagesPath ? $this->_packagesPath:$this->_path;

        // If this is a main class that either extends from Base or Package class
        if (isset($definition['is_main_class']) && $definition['is_main_class']) {
            // If is package then we need to put it in a package subfolder
            if (isset($definition['is_package']) && $definition['is_package']) {
                $writePath = $this->_path . DIRECTORY_SEPARATOR . $definition['package_name'];
            // Otherwise lets just put it in the root of the path
            } else {
                $writePath = $this->_path;
            }

            if ($this->generateTableClasses()) {
                $this->writeTableClassDefinition($definition, $writePath, array('extends' => $definition['inheritance']['tableExtends']));
            }
        }
        // If is the package class then we need to make the path to the complete package
        else if (isset($definition['is_package_class']) && $definition['is_package_class']) {
            if (isset($definition['package_custom_path'])) {
              $writePath = $definition['package_custom_path'];
            } else {
              $writePath = $packagesPath . DIRECTORY_SEPARATOR . $definition['package_path'];
            }

            if ($this->generateTableClasses()) {
                $this->writeTableClassDefinition($definition, $writePath, array('extends' => $definition['inheritance']['tableExtends']));
            }
        }
        // If it is the base class of the doctrine record definition
        else if (isset($definition['is_base_class']) && $definition['is_base_class']) {
            // If it is a part of a package then we need to put it in a package subfolder
            if (isset($definition['is_package']) && $definition['is_package']) {
                $basePath = $this->_path . DIRECTORY_SEPARATOR . $definition['package_name'];
                $writePath = $basePath . DIRECTORY_SEPARATOR . $this->_baseClassesDirectory;
            // Otherwise lets just put it in the root generated folder
            } else {
                $writePath = $this->_path . DIRECTORY_SEPARATOR . $this->_baseClassesDirectory;
            }
        }

        // If we have a writePath from the if else conditionals above then use it
        if (isset($writePath)) {
            $writePath .= DIRECTORY_SEPARATOR . $fileName;
        // Otherwise none of the conditions were met and we aren't generating base classes
        } else {
            $writePath = $this->_path . DIRECTORY_SEPARATOR . $fileName;
        }

        $code = "<?php" . PHP_EOL;

        if (isset($definition['connection']) && $definition['connection']) {
            $code .= "// Connection Component Binding" . PHP_EOL;
            $code .= "Doctrine_Manager::getInstance()->bindComponent('" . $definition['connectionClassName'] . "', '" . $definition['connection'] . "');" . PHP_EOL;
        }

        $code .= PHP_EOL . $definitionCode;

        if(isset($definition['generate_once']) && $definition['generate_once'] === true)
        {
            if(!file_exists($writePath))
            {
                $bytes = file_put_contents($writePath, $code);
            }
        } else
        {
            $bytes = file_put_contents($writePath, $code);
        }

        if(isset($bytes) && $bytes === false)
        {
            throw new Doctrine_Import_Builder_Exception("Couldn't write file " . $writePath);
        }

        Doctrine_Core::loadModel($definition['className'], $writePath);
    }

    /*
     * Build the phpDoc for a class definition
     *
     * @param  array  $definition
     */
    public function buildPhpDocs(array $definition)
    {
        $ret = array();

        $ret[] = $definition['className'];
        $ret[] = '';
        $ret[] = 'This class has been auto-generated by the Doctrine ORM Framework';
        $ret[] = '';

        if ((isset($definition['is_base_class']) && $definition['is_base_class']) || ! $this->generateBaseClasses()) {
            foreach ($definition['columns'] as $name => $column) {
                $name = isset($column['name']) ? $column['name']:$name;
                // extract column name & field name
                if (stripos($name, ' as '))
                {
                    if (strpos($name, ' as')) {
                        $parts = explode(' as ', $name);
                    } else {
                        $parts = explode(' AS ', $name);
                    }

                    if (count($parts) > 1) {
                        $fieldName = $parts[1];
                    } else {
                        $fieldName = $parts[0];
                    }

                    $name = $parts[0];
                } else {
                    $fieldName = $name;
                    $name = $name;
                }

                $name = trim($name);
                $fieldName = trim($fieldName);

                $ret[] = '@property ' . $column['type'] . ' $' . $fieldName;
            }

            if (isset($definition['relations']) && ! empty($definition['relations'])) {
                foreach ($definition['relations'] as $relation) {
                    $type = (isset($relation['type']) && $relation['type'] == Doctrine_Relation::MANY) ? 'Doctrine_Collection' : $this->_classPrefix . $relation['class'];
                    $ret[] = '@property ' . $type . ' $' . $relation['alias'];
                }
            }
            $ret[] = '';
        }

        $ret = ' * ' . implode(PHP_EOL . ' * ', $ret);
        $ret = ' ' . trim($ret);

        return $ret;
    }
}