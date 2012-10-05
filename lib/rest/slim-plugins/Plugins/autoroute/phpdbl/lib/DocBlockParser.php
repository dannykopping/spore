<?php

    // register autoloader
    spl_autoload_register(array('DocBlockParser', 'autoload'));

    /**
     *    A simple PHP DocBlock parser
     *
     * @author Danny Kopping <dannykopping@gmail.com>
     */
    class DocBlockParser
    {
        /**
         * @var string    Regular expression to isolate all annotations
         */
        private $allDocBlockLinesRegex = "%^(\s+)?\*{1}.+[^/]$%m";

        /**
         * @var string    Regular expression to isolate an annotation and its related values
         */
        private $annotationRegex = "/^(@[\w]+)(.+)?$/m";

        /**
         * @var string    Regular expression to split an annotation's values by whitespace
         */
        private $splitByWhitespaceRegex = "/((\t|\s{2,})+)/m";

        /**
         * @var    MethodElement    A reference to the MethodElement currently being used
         */
        private $currentMethod;
        /**
         * @var    AnnotationElement    A reference to the AnnotationElement currently being used
         */
        private $currentAnnotation;

        /**
         * @var    array    An array of parsed ClassElement instances
         */
        private $classes;

        /**
         * @var    array    An array of parsed MethodElement instances
         */
        private $methods;
        /**
         * @var    array    An array of parsed AnnotationElement instances
         */
        private $annotations;

        /**
         * @var null|string    The method filter type(s)
         */
        private $methodFilter;

        /**
         * @var    bool    Whether to allow inherited methods to be parsed
         */
        public $allowInherited = true;

        /**
         *    Create a new DocBlockParser instance
         */
        public function __construct()
        {
            // check for the existence of the Reflection API
            $this->checkCompatibility();
        }

        /**
         * DocBlockParser autoloader
         *
         * Lazy-loads class files when a given class is first referenced.
         *
         * @param $class
         * @return void
         */
        public static function autoload($class)
        {
            // check same directory
            $file = realpath(dirname(__FILE__) . "/" . $class . ".php");

            // if none found, check the element directory
            if (!$file)
                $file = realpath(dirname(__FILE__) . "/element/" . $class . ".php");

            // if found, require_once the sucker!
            if ($file)
                require_once $file;
        }

        /**
         * Set the ReflectionClass' method filter
         *
         * Any combination of ReflectionMethod::IS_STATIC,
         * ReflectionMethod::IS_PUBLIC,
         * ReflectionMethod::IS_PROTECTED,
         * ReflectionMethod::IS_PRIVATE,
         * ReflectionMethod::IS_ABSTRACT,
         * ReflectionMethod::IS_FINAL
         *
         * Example - to parse both protected and public methods:
         * $docParser->setMethodFilter(ReflectionMethod::IS_PROTECTED|ReflectionMethod::IS_PUBLIC);
         *
         * @param $filter null|string    The method filter type(s)
         *
         */
        public function setMethodFilter($filter)
        {
            $this->methodFilter = $filter;
        }

        /**
         * Whether to allow inherited methods to be parsed
         *
         * @param bool    $allow
         */
        public function setAllowInherited($allow)
        {
            $this->allowInherited = $allow;
        }

        /**
         * Analyzes a class or instance for PHP DocBlock comments
         *
         * @param array|string|object    $classes    A single string containing the name of the class to reflect, or an object
         *                                             or an array of these
         */
        public function analyze($classes)
        {
            if (empty($classes))
                return;

            if (!is_array($classes))
                $classes = array($classes);

            foreach ($classes as $classItem)
            {
                if (!is_string($classItem) && !is_object($classItem))
                    throw new Exception("Please pass a valid classname or instance to the DocBlockParser::analyze function");

                $reflector = new ReflectionClass($classItem);

                $class = new ClassElement();
                $class->setReflectionObject($reflector);
                $class->name = $reflector->getName();

                $this->methods = array();
                $this->annotations = array();

                // a bug in the ReflectionClass makes getMethods behave unexpectedly when passed a NULL
                $methods = (!$this->methodFilter)
                    ? $reflector->getMethods()
                    : $reflector->getMethods($this->methodFilter);

                foreach ($methods as $method)
                {
                    $this->currentAnnotation = null;

                    if (!$this->allowInherited && $method->class !== $class->name)
                        continue;

                    $m = new MethodElement($class);
                    $m->name = $method->getName();
                    $m->setReflectionObject($method);

                    preg_match_all($this->allDocBlockLinesRegex, $method->getDocComment(), $result, PREG_PATTERN_ORDER);
                    for ($i = 0; $i < count($result[0]); $i++)
                    {
                        $this->currentMethod =& $m;
                        $this->parse($result[0][$i]);
                    }

                    $this->methods[] = $m;
                    $class->addMethod($m);
                }

                $this->classes[] = $class;
            }
        }

        /**
         * Parses a PHP DocBlock to construct MethodElement and AnnotationElement instances
         * based on the contents
         *
         * @param $string    The PHP DocBlock string
         */
        protected function parse($string)
        {
            $an = new AnnotationElement($this->currentMethod);
            $this->currentMethod->addAnnotation($an);

            // strip first instance of asterisk
            $string = substr($string, strpos($string, "*") + 1);
            $string = trim($string);

            // find all the individual annotations
            preg_match_all($this->annotationRegex, $string, $result, PREG_PATTERN_ORDER);

            if (!empty($result[1]))
            {
                for ($i = 0; $i < count($result[2]); $i++)
                {
                    if (!empty($result[2]))
                    {
                        $an->name = $result[1][0];
                        $an->values = preg_split($this->splitByWhitespaceRegex, trim($result[2][$i]), null);
                    }
                }

                $this->annotations[] = $this->currentAnnotation = $an;
            }
            else
            {
                // if there is text inside the PHP DocBlock, it may either relate to the method as a description
                // or to an annotation as a multi-line description. If there is no current annotation, then the
                // descriptive text is declared before any annotations, so it is probably a method description; otherwise
                // it probably relates to an annotation

                if (!$this->currentAnnotation)
                {
                    $this->currentMethod->description .= $string . "\n";
                }
                else
                {
                    if (!empty($this->currentAnnotation->values))
                        $this->currentAnnotation->values[count($this->currentAnnotation->values) - 1] .= "\n" . $string;
                }
            }
        }

        /**
         * Get an array of parsed ClassElement instances
         *
         * @return array[ClassElement]|null
         */
        public function getClasses()
        {
            if (!$this->classes || empty($this->classes))
                return null;

            return $this->classes;
        }

        /**
         * Get an array of all the parsed methods with their related annotations
         *
         * @return array[MethodElement]
         */
        public function getMethods()
        {
            if (!$this->methods || empty($this->methods))
                return null;

            return $this->methods;
        }

        /**
         * Get an array of all the parsed annotations
         *
         * @param array    $filter    (optional) Filter by annotation name
         * @return array[AnnotationElement]
         */
        public function getAnnotations($filter = null)
        {
            if (!$this->annotations || empty($this->annotations))
                return null;

            if (!$this->methods || empty($this->methods))
                return null;

            $annotations = array();
            foreach ($this->methods as $method)
            {
                $methodAnnotations = $method->getAnnotations($filter);
                array_merge($annotations, $methodAnnotations);
            }

            return $annotations;
        }

        /**
         * Check to see if all dependencies are satisfied
         *
         * @throws Exception
         */
        protected function checkCompatibility()
        {
            if (!class_exists("Reflection"))
                throw new Exception("Fatal error: Dependency 'Reflection API' not met. PHP5 is required.");
        }
    }

?>