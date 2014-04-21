<?php
namespace Spore\Model;

use Spore\Annotation\AbstractAnnotation;
use Spore\Annotation\BaseAnnotation;
use Spore\Annotation\URIAnnotation;
use Spore\Container;
use Spore\Traits\ContainerAware;

/**
 * @author Danny Kopping
 */
class Route
{
    use ContainerAware;

    /**
     * @var AbstractAnnotation[]
     */
    protected $annotations;

    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback, Container $container, array $annotations = array())
    {
        $this->annotations = [];

        if (!count($annotations)) {
            return;
        }

        foreach ($annotations as $annotation) {
            $this->addAnnotation($annotation);
        }

        $this->setContainer($container);
        $this->setCallback($callback);
    }

    /**
     * @param AbstractAnnotation $annotation
     */
    public function addAnnotation(AbstractAnnotation $annotation)
    {
        if (empty($annotation)) {
            return;
        }

        $this->annotations[strtolower($annotation->getIdentifier())] = $annotation;
    }

    /**
     * Return the full URI for this route, based on the @base & @uri annotation
     *
     * @return string
     */
    public function getURI()
    {
        $container = $this->getContainer();

        $base = $this->getAnnotationByName($container[Container::BASE_ANNOTATION]);
        $uri  = $this->getAnnotationByName($container[Container::URI_ANNOTATION]);

        if ($base) {
            $base = $base->getRaw()->getValue();
        }

        if ($uri) {
            $uri = $uri->getRaw()->getValue();
        }

        return $base . $uri;
    }

    /**
     * @param $name
     *
     * @return null|AbstractAnnotation
     */
    public function getAnnotationByName($name)
    {
        if (!isset($this->annotations[$name])) {
            return null;
        }

        return $this->annotations[$name];
    }

    /**
     * @return AbstractAnnotation[]
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @param callable $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Execute the route, calling pre- and post-execution callbacks
     *
     * @return mixed
     */
    public function execute()
    {
        $container = $this->getContainer();
        $before    = $container[Container::BEFORE_CALLBACK];
        $callback  = $container[Container::CALLBACK_WRAPPER];
        $after     = $container[Container::AFTER_CALLBACK];

        call_user_func_array($before, [$this]);
        $result = call_user_func_array($callback, [$this]);
        call_user_func_array($after, [$this, $result]);

        return $result;
    }
} 