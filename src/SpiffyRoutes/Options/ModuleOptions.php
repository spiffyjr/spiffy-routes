<?php

namespace SpiffyRoutes\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $cacheAdapter = 'SpiffyRoutes\Cache';

    /**
     * @param string $cacheAdapter
     * @return ModuleOptions
     */
    public function setCacheAdapter($cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }
}