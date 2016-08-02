<?php

/**
 * Cache helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
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
        'getAccountDetails',
        'listInterestGroupings',
        'listMemberActivity',
        'listMemberInfo',
        'listMergeVars',
        'lists',
        'listsForEmail'
    );

    /**
     * Cache tags unique param ID
     *
     * @var array
     * @access protected
     */
    protected $_cacheTagId = array(
        'listMemberInfo' => array('id', 'email_address'),
        'listMemberActivity' => array('id', 'email_address'),
        'listsForEmail' => array('email_address'),
    );

    /**
     * Clear cache callbacks
     *
     * @var array
     * @access protected
     */
    protected $_cacheClearCallbacks = array(
        'listUnsubscribe' => array('listMemberInfo', 'listMembers', 'listMemberActivity', 'listsForEmail', 'lists'),
        'listSubscribe' => array('listMemberInfo', 'listMembers', 'listMemberActivity', 'listsForEmail', 'lists'),
        'listUpdateMember' => array('listMemberInfo', 'listMembers', 'listMemberActivity', 'listsForEmail', 'lists'),
    );

    /**
     * Retrieve cache key to save data in cache storage
     *
     * @param string $command
     * @param string $args
     * @param string OPTIONAL $apiKey
     * @return string
     */
    public function cacheKey($command, $args, $apiKey = null)
    {

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
    public function clearCache($command, $object)
    {
        if (FALSE === array_key_exists($command, $this->_cacheClearCallbacks)) {
            return FALSE;
        }

        foreach ($this->_cacheClearCallbacks[$command] as $cmd) {
            Mage::app()->cleanCache($this->cacheTagForCommand($cmd, $object));
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
    public function cacheTagForCommand($command, $object)
    {
        $tag = $command;

        if (isset($this->_cacheTagId[$command])) {
            foreach ($this->_cacheTagId[$command] as $param) {
                $tag .= $object->requestParams[$param];
            }
        }

        $tag = array(strtoupper($tag));

        return $tag;
    }

}
