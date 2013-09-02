<?php

/**
 * Cache helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Helper_Cache extends Mage_Core_Helper_Abstract
{

    /**
     * Cacheable API commands
     *
     * @var array
     * @access protected
     */
    protected $_cacheableCommands = array(
        'helper/account-details',
        'lists/interest-groupings',
        'lists/member-activity',
        'lists/member-info',
        'lists/merge-vars',
        'lists/list',
        'helper/lists-for-email'
    );

    /**
     * Cache tags unique param ID
     *
     * @var array
     * @access protected
     */
    protected $_cacheTagId = array(
        'lists/member-info' => array('id', 'email_address'),
        'lists/member-activity' => array('id', 'email_address'),
        'helper/lists-for-email' => array( 'email_address'),
    );

    /**
     * Clear cache callbacks
     *
     * @var array
     * @access protected
     */
    protected $_cacheClearCallbacks = array(
        'lists/unsubscribe' => array('lists/member-info', 'lists/member-activity',  'helper/lists-for-email', 'lists/list'),
        'lists/subscribe' => array('lists/member-info', 'lists/member-activity',  'helper/lists-for-email', 'lists/list'),
        'lists/update-member' => array('lists/member-info', 'lists/member-activity', 'helper/lists-for-email', 'lists/list'),
    );

    /**
     * Retrieve cache key to save data in cache storage
     *
     * @param string $command
     * @param string $args
     * @param string OPTIONAL $apiKey
     * @return string
     */
    public function cacheKey($command, $args, $apiKey = null) {

        if (FALSE === in_array($command, $this->_cacheableCommands)) {
            return FALSE;
        }

        if (is_null($args)) {
            $args = array();
        }

        return md5($command . serialize($args) . $apiKey);
    }

    /**
     * Clear data from Cache
     *
     * @param string $command
     * @param object $object Request object
     * @return Ebizmarts_MageMonkey_Helper_Cache
     */
    public function clearCache($command, $args) {
        if (FALSE === array_key_exists($command, $this->_cacheClearCallbacks)) {
            return FALSE;
        }

        foreach ($this->_cacheClearCallbacks[$command] as $cmd) {
            Mage::app()->cleanCache($this->cacheTagForCommand($cmd, $args));
        }

        return $this;
    }

    /**
     * Return cache TAG for given command
     *
     * @param string $command
     * @param object $object Request object
     * @return array
     */
    public function cacheTagForCommand($command, $args) {
        $tag = $command;

        if (isset($this->_cacheTagId[$command])) {
            foreach ($this->_cacheTagId[$command] as $param) {
                $tag .= $param;
            }
        }

        $tag = array(strtoupper($tag));

        return $tag;
    }

}
