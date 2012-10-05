<?php
    /**
     *    Defines a method element relating to a DocBlock
     */
    class MethodElement extends AbstractElement
    {
        public $name;

        private $annotations = array();

        public $description;

        private $class;

        public function __construct(ClassElement $class)
        {
            $this->class = $class;
        }

        /**
         * Add an annotation
         *
         * @param AnnotationElement $annotation
         */
        public function addAnnotation(AnnotationElement $annotation)
        {
            if (empty($this->annotations))
                $this->annotations = array();

            $this->annotations[] = $annotation;
        }

        /**
         * Get an array of all the parsed annotations
         *
         * @param array|string    $filter    (optional) Filter by annotation name
         * @return array[AnnotationElement]
         */
        public function getAnnotations($filter = null)
        {
            if (!$this->annotations || empty($this->annotations))
                return null;

            if (!$filter)
                return $this->annotations;

            if (is_string($filter))
                $filter = array($filter);

            $annotations = array();
            if ($filter)
            {
                foreach ($this->annotations as $annotation)
                {
                    // chop off the @ at the beginning of the attribute name
                    $withoutAnnotationMarker = substr($annotation->name, 1);
                    if (in_array($annotation->name, $filter) || in_array($withoutAnnotationMarker, $filter))
                        $annotations[] = $annotation;
                }
            }
            else
            {
                array_merge($annotations, $this->annotations);
            }

            return $annotations;
        }

        /**
         * Returns the first annotation found with a matching name
         *
         * @param    string    $filter
         * @return null|array[AnnotationElement]
         */
        public function getAnnotation($filter)
        {
            $annotations = $this->getAnnotations($filter);
            if (empty($annotations))
                return null;

            return $annotations[0];
        }

        /**
         * Determines whether this MethodElement instance contains an annotation of a certain type
         *
         * @param string $filter    An annotation name
         * @return bool
         */
        public function hasAnnotation($filter)
        {
            $annotations = $this->getAnnotations($filter);
            return !empty($annotations);
        }

        /**
         * Determines whether this MethodElement instance contains all annotations of certain types
         *
         * @param array $filters    An array of annotation names
         * @return bool
         */
        public function hasAnnotations($filters)
        {
            if (empty($filters) || !is_array($filters))
                return false;

            foreach ($filters as $filter)
            {
                $annotations = $this->getAnnotations($filter);
                if (empty($annotations))
                    return false;
            }

            return true;
        }

        /**
         * Get the related ClassElement instance
         *
         * @return ClassElement
         */
        public function getClass()
        {
            return $this->class;
        }
    }

?>