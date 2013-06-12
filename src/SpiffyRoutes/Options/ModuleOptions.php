<?php

namespace SpiffyRoutes\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $cacheAdapter = 'Zend\Cache\Storage\Adapter\Memory';

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