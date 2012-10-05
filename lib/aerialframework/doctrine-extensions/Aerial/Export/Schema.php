<?php
    class Aerial_Export_Schema extends Doctrine_Export_Schema
    {
        /**
         * - Extension of Doctrine's Doctrine_Export_Schema class
         *
         * buildSchema
         *
         * Build schema array that can be dumped to file
         *
         * @param string $directory  The directory of models to build the schema from
         * @param array $models      The array of model names to build the schema for
         * @param integer $modelLoading The model loading strategy to use to load the models from the passed directory
         * @return void
         */
        public function buildSchema($directory = null, $models = array(), $modelLoading = null)
        {
            if($directory !== null)
            {
                $loadedModels = Doctrine_Core::filterInvalidModels(Doctrine_Core::loadModels($directory, $modelLoading));
            } else
            {
                $loadedModels = Doctrine_Core::getLoadedModels();
            }

            $array = array();

            $parent = new ReflectionClass('Doctrine_Record');

            $sql = array();
            $fks = array();

            // we iterate through the diff of previously declared classes
            // and currently declared classes
            foreach($loadedModels as $className)
            {
                if(!empty($models) && !in_array($className, $models))
                {
                    continue;
                }

                $recordTable = Doctrine_Core::getTable($className);

                $data = $recordTable->getExportableFormat();

                $table = array();
                $remove = array('ptype', 'ntype', 'alltypes');
                // Fix explicit length in schema, concat it to type in this format: type(length)
                foreach($data['columns'] AS $name => $column)
                {
                    if(isset($column['length']) && $column['length'] && isset($column['scale']) && $column['scale'])
                    {
                        $data['columns'][$name]['type'] = $column['type'] . '(' . $column['length'] . ', ' . $column['scale'] . ')';
                        unset($data['columns'][$name]['length'], $data['columns'][$name]['scale']);
                    } else
                    {
                        $data['columns'][$name]['type'] = $column['type'] . '(' . $column['length'] . ')';
                        unset($data['columns'][$name]['length']);
                    }
                    // Strip out schema information which is not necessary to be dumped to the yaml schema file
                    foreach($remove as $value)
                    {
                        if(isset($data['columns'][$name][$value]))
                        {
                            unset($data['columns'][$name][$value]);
                        }
                    }

                    // If type is the only property of the column then lets abbreviate the syntax
                    // columns: { name: string(255) }
                    if(count($data['columns'][$name]) === 1 && isset($data['columns'][$name]['type']))
                    {
                        $type = $data['columns'][$name]['type'];
                        unset($data['columns'][$name]);
                        $data['columns'][$name] = $type;
                    }
                }
                $table['tableName'] = $data['tableName'];
                $table['columns'] = $data['columns'];

                $relations = $recordTable->getRelations();
                foreach($relations as $key => $relation)
                {
                    $relationData = $relation->toArray();

                    $relationKey = $relationData['alias'];

                    if(isset($relationData['refTable']) && $relationData['refTable'])
                    {
                        $table['relations'][$relationKey]['refClass'] = $relationData['refTable']->getComponentName();
                    }

                    if(isset($relationData['class']) && $relationData['class'] && $relation['class'] != $relationKey)
                    {
                        $table['relations'][$relationKey]['class'] = $relationData['class'];
                    }

                    $table['relations'][$relationKey]['local'] = $relationData['local'];
                    $table['relations'][$relationKey]['foreign'] = $relationData['foreign'];

                    if($relationData['type'] === Doctrine_Relation::ONE)
                    {
                        $table['relations'][$relationKey]['type'] = 'one';
                    } else if($relationData['type'] === Doctrine_Relation::MANY)
                    {
                        $table['relations'][$relationKey]['type'] = 'many';
                    } else
                    {
                        $table['relations'][$relationKey]['type'] = 'one';
                    }
                }

                $array[$className] = $table;
            }

            return $array;
        }
    }
