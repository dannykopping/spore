<?php

    require_once "lib/DocBlockParser.php";

    $d = new DocBlockParser();
    $d->setAllowInherited(true);
    $d->setMethodFilter(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
    $d->analyze(array("TestClass"));

    $classes = $d->getClasses();

    foreach ($classes as $class)
    {
        echo "Class: " . $class->name . "\n";

        $methods = $class->getMethods();
        foreach ($methods as $method)
        {
            $annotations = $method->getAnnotations(array("param", "author"));

            echo "Method: " . $method->getClass()->name . "::" . $method->name . "\n";
            echo "Description: " . $method->description . "\n";

            if (empty($annotations))
                continue;

            foreach ($annotations as $annotation)
            {
                echo "\tAnnotation: " . $annotation->name . "\n";
                echo "\tValues: " . print_r($annotation->values, true) . "\n";
            }

            echo str_repeat("-", 50) . "\n";
        }
    }

    class BaseClass
    {
        /**
         * Test function 1
         */
        public function testFunc1()
        {

        }

        /**
         * Test function 2
         */
        protected function testFunc2()
        {

        }
    }

    class TestClass extends BaseClass
    {
        /**
         * This is the DocBlock description
         * @param $data  The data to be passed in
         */
        public function test($data)
        {
        }

        /**
         * This is another DocBlock description
         * @param $data  The data to be passed in
         * @author    Danny Kopping
         */
        protected function test2($data)
        {
        }
    }

?>