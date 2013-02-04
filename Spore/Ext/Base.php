<?php
namespace Spore\Ext;

use Slim\Slim;
use Spore\Spore;

/**
 *    A base class for all Slim "plugins"
 */
abstract class Base
{
    protected $slimInstance;

    protected $args;

    public function __construct(Slim &$slimInstance, $args = null)
    {
        $this->slimInstance =& $slimInstance;

        if (!empty($args)) {
            $this->args = $args;
        }
    }

    /**
     * @return Spore
     */
    protected function getSlimInstance()
    {
        return $this->slimInstance;
    }
}
