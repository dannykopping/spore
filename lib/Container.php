<?php
namespace Spore;

use Pimple;
use Spore\Service\RouteInspector;
use Spore\Service\TargetMapAnalyser;

/**
 * @author Danny Kopping
 */
class Container extends Pimple
{
    const TARGET_MAP_ANALYSER = 'target-map-analyser';
    const ROUTE_INSPECTOR     = 'route-inspector';

    public function initialise()
    {
        $this[self::TARGET_MAP_ANALYSER] = function () {
            return new TargetMapAnalyser();
        };

        $this[self::ROUTE_INSPECTOR] = function () {
            return new RouteInspector();
        };
    }
} 