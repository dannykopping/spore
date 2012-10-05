<?php
/**
 * Created by IntelliJ IDEA.
 * User: Danny Kopping
 * Date: 2011/01/19
 * Time: 12:07 AM
 */

class Aerial_Import extends Doctrine_Import
{

    /**
     * importSchema
     *
     * method for importing existing schema to Doctrine_Record classes (overriden by Aerial)
     *
     * @param string $directory
     * @param array $databases
     * @return array                the names of the imported classes
     */
    public function importSchema($directory, array $databases = array(), array $options = array())
    {
        $connections = Doctrine_Manager::getInstance()->getConnections();
        $classes = array();

        $defs = array();

        foreach ($connections as $name => $connection)
        {
            // Limit the databases to the ones specified by $databases.
            // Check only happens if array is not empty
            if ( ! empty($databases) && ! in_array($name, $databases)) {
            continue;
            }

            $builder = new Aerial_Import_Builder();
            $builder->setTargetPath($directory);
            $builder->setOptions($options);

            $definitions = array();

            foreach ($connection->import->listTables() as $table) {
              $definition = array();
              $definition['tableName'] = $table;
              $definition['className'] = Doctrine_Inflector::classify(Doctrine_Inflector::tableize($table));
              $definition['columns'] = $connection->import->listTableColumns($table);
              $definition['connection'] = $connection->getName();
              $definition['connectionClassName'] = $definition['className'];

              try {
                  $definition['relations'] = array();
                  $relations = $connection->import->listTableRelations($table);
                  $relClasses = array();
                  foreach ($relations as $relation) {
                      $table = $relation['table'];
                      $class = Doctrine_Inflector::classify(Doctrine_Inflector::tableize($table));
                      if (in_array($class, $relClasses)) {
                          $alias = $class . '_' . (count($relClasses) + 1);
                      } else {
                          $alias = $class;
                      }
                      $relClasses[] = $class;
                      $definition['relations'][$alias] = array(
                          'alias'   => $alias,
                          'class'   => $class,
                          'local'   => $relation['local'],
                          'foreign' => $relation['foreign']
                      );
                  }
              } catch (Exception $e) {}

              $definitions[strtolower($definition['className'])] = $definition;
              $classes[] = $definition['className'];
            }

            // Build opposite end of relationships
            foreach ($definitions as $definition) {
              $className = $definition['className'];
              $relClasses = array();
              foreach ($definition['relations'] as $alias => $relation) {
                  if (in_array($relation['class'], $relClasses) || isset($definitions[$relation['class']]['relations'][$className])) {
                      $alias = $className . '_' . (count($relClasses) + 1);
                  } else {
                      $alias = $className;
                  }
                  $relClasses[] = $relation['class'];
                  $definitions[strtolower($relation['class'])]['relations'][$alias] = array(
                    'type' => Doctrine_Relation::MANY,
                    'alias' => $alias,
                    'class' => $className,
                    'local' => $relation['foreign'],
                    'foreign' => $relation['local']
                  );
              }
            }

            foreach ($definitions as $definition) {
              $defs[] = $builder->buildRecord($definition);
            }
        }

        return $defs;
    }
}
