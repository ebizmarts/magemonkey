<?php

/**
 * Module's cache model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Cache {

    /**
     * @var bool Store if cache type is enabled
     */
    protected $_isEnabled;

    /**
     * @var array Store cache tags
     */
    protected $_cacheTags = array(self::CACHE_TAG);

    /**
     * @var int|null Cache lifetime in seconds or NULL for infinite lifetime
     */
    protected $_cacheLifetime = NULL;
    
    /**
     * @const CACHE_TAG General cache tag
     */
    const CACHE_TAG = 'MONKEY_GENERAL_CACHE_TAG';

    /**
     * @const CACHE_ID Cache ID
     */
    const CACHE_ID = 'monkey';

    /**
     * Class constructor
     */
    public function __construct() {
        $this->_isEnabled = Mage::app()->useCache(self::CACHE_ID);
    }

    /**
     * Check if <monkey> cache is enabled
     *
     * @return bool
     */
    public function isCacheEnabled() {
        return (bool) $this->_isEnabled;
    }

    /**
     * Return cache tags
     *
     * @return array Cache tags
     */
    public function getCacheTags() {
        return $this->_cacheTags;
    }

    /**
     * Return cache lifetime
     *
     * @return null|int
     */
    public function getCacheLifetime() {
        return $this->_cacheLifetime;
    }

    /**
     * Save data to cache
     *
     * @param string $data Data to be cached
     * @param string $cacheId
     * @return Ebizmarts_MageMonkey_Model_Cache
     */
    public function saveCacheData($data, $cacheId, $tags = array()) {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        $cacheTags = (!empty($tags)) ? array_merge($this->getCacheTags(), $tags) : $this->getCacheTags();

        Mage::app()->saveCache($data, $cacheId, $cacheTags, $this->getCacheLifetime());

        return $this;
    }

    /**
     * Retrieve data from Cache
     *
     * @param string $cacheId Cache ID
     * @return mixed Cache data
     */
    public function loadCacheData($cacheId) {
        if (!$this->isCacheEnabled()) {
            return FALSE;
        }

        return Mage::app()->loadCache($cacheId);
    }

    /**
     * Remove data from Cache
     *
     * @param string $cacheId Cache ID
     * @return Ebizmarts_MageMonkey_Model_Cache
     */
    public function removeCacheData($cacheId) {
        if (!$this->isCacheEnabled()) {
            return FALSE;
        }

        Mage::app()->removeCache($cacheId);

        return $this;
    }

    /**
     * Clean <monkey> cache
     *
     * @return Ebizmarts_MageMonkey_Model_Cache
     */
    public function cleanCache() {
        Mage::app()->cleanCache(self::CACHE_TAG);
        return $this;
    }

    /**
     * Invalidate <monkey> cache
     *
     * @return Ebizmarts_MageMonkey_Model_Cache
     */
    public function invalidateCache() {
        Mage::app()->getCacheInstance()->invalidateType(self::CACHE_ID);
        return $this;
    }    
    
}