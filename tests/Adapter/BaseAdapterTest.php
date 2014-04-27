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
     * @dataProvider routerDataProvider
     */
    public function testAdapterCreation($router)
    {
        $spore = new Spore();

        $adapter = $spore->createAdapter($this->getAdapterName(), $router);
        $this->assertInstanceOf('\\Spore\\Adapter\\BaseAdapter', $adapter);
        $this->assertInstanceOf($this->getMainClassNamespace(), $adapter->getRouter());
    }

    abstract public function getAdapterName();

    abstract public function getMainClassNamespace();

    abstract public function routerDataProvider();
}