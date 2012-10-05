<?php
class Aerial_Import_Schema extends Doctrine_Import_Schema
{

    /**
     * importSchema
     *
     * A method to import a Schema and translate it into a Doctrine_Record object (overriden by Aerial)
     *
     * @param  string $schema       The file containing the XML schema
     * @param  string $format       Format of the schema file
     * @param  string $directory    The directory where the Doctrine_Record class will be written
     * @param  array  $models       Optional array of models to import
     *
     * @return void
     */
    public function importSchema($schema, $format = 'yml', $directory = null, $models = array())
    {
        $schema = (array) $schema;
        $definitions = array();

        $builder = new Aerial_Import_Builder();
        $builder->setTargetPath($directory);

	    $options = $this->getOptions();
	    $packageName = $options["actionscriptPackageName"];

	    unset($options["actionscriptPackageName"]);

	    $builder->actionscriptPackageName = $packageName;

        $builder->setOptions($this->getOptions());

        $array = $this->buildSchema($schema, $format);

        if (count($array) == 0) {
            throw new Doctrine_Import_Exception(
                sprintf('No ' . $format . ' schema found in ' . implode(", ", $schema))
            );
        }

        foreach ($array as $name => $definition) {
            if ( ! empty($models) && !in_array($definition['className'], $models)) {
                continue;
            }

            $definitions[] = $builder->buildRecord($definition);
        }

        return $definitions;
    }

    /**
     * importSchema
     *
     * A method to import a Schema and translate it into a Doctrine_Record object (overriden by Aerial)
     *
     * @param  string $schema       The file containing the XML schema
     * @param  string $format       Format of the schema file
     * @param  string $directory    The directory where the Doctrine_Record class will be written
     * @param  array  $models       Optional array of models to import
     *
     * @return void
     */
    public function importEmulatedSchema($schema, $format = 'yml', $directory = null, $models = array())
    {
        $schema = (array) $schema;
        $definitions = array();

        $builder = new Aerial_Import_Builder();
        $builder->setTargetPath($directory);

	    $options = $this->getOptions();
	    $packageName = $options["actionscriptPackageName"];

	    unset($options["actionscriptPackageName"]);

	    $builder->actionscriptPackageName = $packageName;

        $builder->setOptions($this->getOptions());

        $array = $this->buildSchema($schema, $format);

        if (count($array) == 0) {
            throw new Doctrine_Import_Exception(
                sprintf('No ' . $format . ' schema found in ' . implode(", ", $schema))
            );
        }

        foreach ($array as $name => $definition) {
            if ( ! empty($models) && !in_array($definition['className'], $models)) {
                continue;
            }

            $definitions[$definition['className']] = $builder->buildEmulatedRecord($definition);
        }

        return $definitions;
    }
}
