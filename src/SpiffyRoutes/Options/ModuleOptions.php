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
     * @var array
     */
    protected $excluded = array();

    /**
     * @param array $excluded
     * @return ModuleOptions
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

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