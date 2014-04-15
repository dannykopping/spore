<?php
namespace Spore\Service;

use Composer\Autoload\ClassLoader;
use Spore\Exception\TargetException;

/**
 * @author Danny Kopping
 */
class TargetMapAnalyser extends Base
{
    /**
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * @var array
     */
    protected $namespaceTargets;

    public function run()
    {
        $classLoader      = $this->getClassLoader();
        $namespaceTargets = $this->getNamespaceTargets();

        if (empty($classLoader)) {
            throw new TargetException(TargetException::MISSING_CLASS_LOADER);
        }

        if (empty($namespaceTargets)) {
            throw new TargetException(TargetException::MISSING_NAMESPACE_TARGETS);
        }

        $classMap  = $classLoader->getClassMap();
        $targetMap = [];

        if (empty($classMap)) {
            return $targetMap;
        }

        // scan the Composer classmap and find matches based on given namespace targets
        // ... include the classes that exist
        foreach ($classMap as $namespace => $filename) {
            foreach ($namespaceTargets as $namespaceTarget) {
                if (strpos($namespace, $namespaceTarget) !== false) {
                    if (!class_exists($namespace)) {
                        continue;
                    }

                    $targetMap[] = $namespace;
                }
            }
        }

        return $targetMap;
    }

    /**
     * @param \Composer\Autoload\ClassLoader $classLoader
     */
    public function setClassLoader($classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public function getClassLoader()
    {
        return $this->classLoader;
    }

    /**
     * @param array $namespaceTargets
     */
    public function setNamespaceTargets($namespaceTargets)
    {
        $this->namespaceTargets = $namespaceTargets;
    }

    /**
     * @return array
     */
    public function getNamespaceTargets()
    {
        return $this->namespaceTargets;
    }
}