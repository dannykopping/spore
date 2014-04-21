<?php
namespace Spore\Factory;

use Spore\Adapter\BaseAdapter;
use Spore\Container;
use stdClass;

/**
 * @author Danny Kopping
 */
class AdapterFactory extends AbstractFactory
{
    /**
     * @param $name
     * @param $adaptee
     *
     * @return null|BaseAdapter
     */
    public function createByName($name, $adaptee)
    {
        $adapters = array_change_key_case(self::getAdapterClasses(), CASE_LOWER);
        if (!isset($adapters[$name])) {
            return null;
        }

        $adapterClass = $adapters[$name];
        return new $adapterClass($this->getContainer(), $adaptee);
    }

    /**
     * Retrieve an array of adapter classes, name as key => namespace as value
     *
     * return array
     */
    private function getAdapterClasses()
    {
        $container = $this->getContainer();
        return $container[Container::ADAPTER_CLASSES];
    }
}