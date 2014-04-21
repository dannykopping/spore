<?php
use Spore\Spore;

/**
 * @group   adapters
 */
abstract class BaseAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testAdapterInstallation()
    {
        $adapter = $this->getAdapterName();
        $ns      = $this->getMainClassNamespace();

        $this->assertTrue(
            class_exists($ns),
            sprintf('%s installation not found - please run `composer install --dev`', $adapter)
        );
    }

    /**
     * @dataProvider adapteeDataProvider
     */
    public function testAdapterCreation($adaptee)
    {
        $spore = new Spore();

        $adapter = $spore->createAdapter($this->getAdapterName(), $adaptee);
        $this->assertInstanceOf('\\Spore\\Adapter\\BaseAdapter', $adapter);
        $this->assertInstanceOf($this->getMainClassNamespace(), $adapter->getAdaptee());
    }

    abstract public function getAdapterName();

    abstract public function getMainClassNamespace();

    abstract public function adapteeDataProvider();
}