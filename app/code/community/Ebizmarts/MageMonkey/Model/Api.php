<?php

/**
 * MailChimp API Magento wrapper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Api {

    /**
     * Api instance
     *
     * @var Ebizmarts_MageMonkey_Model_MCAPI|Ebizmarts_MageMonkey_Model_MCEXPORTAPI
     * @access protected
     */
    protected $_mcapi = null;

    /**
     * Api host
     *
     * @var string
     * @access protected
     */
    protected $_apihost = null;

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
        'listMembers',
        'listMergeVars',
        'lists',
        'listsForEmail'
    );

    /**
     * Clear cache callbacks
     *
     * @var array
     * @access protected
     */
    protected $_cacheClearCallbacks = array(
        'listUnsubscribe' => array('listMemberInfo', 'listMembers', 'listMemberActivity',  'listsForEmail'),
        'listSubscribe' => array('listMemberInfo', 'listMembers', 'listMemberActivity',  'listsForEmail'),
        'listUpdateMember' => array('listMemberInfo', 'listMembers', 'listMemberActivity', 'listsForEmail'),
    );

    /**
     * Cache tags unique param ID
     *
     * @var array
     * @access protected
     */
    protected $_cacheTagId = array(
        'listMemberInfo' => array('id', 'email_address'),
        'listMembers' => array('id', 'status'),
        'listMemberActivity' => array('id', 'email_address'),
        'listsForEmail' => array( 'email_address'),
    );

    /**
     * MC API error code if any
     *
     * @var integer
     * @access public
     */
    public $errorCode = null;

    /**
     * MC API error message if any
     *
     * @var string
     * @access public
     */
    public $errorMessage = null;

    /**
     * Initialize API
     *
     * @param array $args
     * @return void
     */
    public function __construct($args) {
        $storeId = isset($args['store']) ? $args['store'] : null;
        $apikey = (!isset($args['apikey']) ? Mage::helper('monkey')->getApiKey($storeId) : $args['apikey']);

        if (isset($args['_export_'])) {
            $this->_mcapi = new Ebizmarts_MageMonkey_Model_MCEXPORTAPI($apikey);
        } else {
            $this->_mcapi = new Ebizmarts_MageMonkey_Model_MCAPI($apikey);
        }

        //Create actual API URL using API key, borrowed from MCAPI.php
        $dc = "us1";
        if (strstr($this->_mcapi->api_key, "-")) {
            list($key, $dc) = explode("-", $this->_mcapi->api_key, 2);
            if (!$dc)
                $dc = "us1";
        }
        $this->_apihost = $dc . "." . $this->_mcapi->apiUrl["host"];
    }

    /**
     * Magic __call method
     *
     * @link http://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args = null) {
        $this->errorCode = null;
        $this->errorMessage = null;

        return $this->call($method, $args);
    }

    /**
     * Perform API call, also can be used "directly"
     *
     * @param string $command Command to be performed
     * @param array $args OPTIONAL call parameters
     * @return mixed
     */
    public function call($command, $args) {
        try {

            $cacheKey = $this->_cacheKey($command, $args);

            $this->_logApiCall($this->_apihost);
            $this->_logApiCall($this->_mcapi->api_key);
            $this->_logApiCall($command);
            $this->_logApiCall($args);
            
            //If there is NO cache key it means that we cannot cache methods data
            if ($cacheKey) {
                $cache = Mage::getModel('monkey/cache');
                $cacheData = $cache->loadCacheData($cacheKey);

                if ($cacheData) {

                    $result = unserialize($cacheData);

                    $this->_logApiCall('------ START Data from Cache for `' . $command . '` ------');
                    $this->_logApiCall($result);
                    $this->_logApiCall('------ FINISH Data from Cache for `' . $command . '` ------');

                    return $result;
                }
            }

            if ($args) {
                $result = call_user_func_array(array($this->_mcapi, $command), $args);
            } else {
                $result = $this->_mcapi->{$command}();
            }

            $this->_logApiCall($result);

            if ($this->_mcapi->errorMessage) {
                $this->_logApiCall("Error: {$this->_mcapi->errorMessage}, code {$this->_mcapi->errorCode}");

                $this->errorCode = $this->_mcapi->errorCode;
                $this->errorMessage = $this->_mcapi->errorMessage;
                
                //Clear associated cache for this call, for example clear cache for listsForEmail when executing listUnsubscribe
                $this->_clearCache($command);

                return (string) $this->_mcapi->errorMessage;
            }

            if ($cacheKey) {
                $cache->saveCacheData(serialize($result), $cacheKey, $this->_cacheTagForCommand($command));
            }
            
            //Clear associated cache for this call, for example clear cache for listsForEmail when executing listUnsubscribe
            $this->_clearCache($command);

            return $result;
        } catch (Exception $ex) {

            Mage::logException($ex);

            return $ex->getMessage();
        }
        return FALSE;
    }

    /**
     * Clear data from Cache
     *
     * @param string $command
     * @param string $args
     * @param string OPTIONAL $apikey
     * @return Ebizmarts_MageMonkey_Model_Api
     */
    protected function _clearCache($command) {
        if (FALSE === array_key_exists($command, $this->_cacheClearCallbacks)) {
            return FALSE;
        }

        foreach ($this->_cacheClearCallbacks[$command] as $cmd) {
            $this->_logApiCall('CLEAN CACHE TAG');
            Mage::app()->cleanCache($this->_cacheTagForCommand($cmd));
        }

        return $this;
    }

    protected function _cacheTagForCommand($command) {
        $tag = $command;

        if (isset($this->_cacheTagId[$command])) {
            foreach ($this->_cacheTagId[$command] as $param) {
                $tag .= $this->_mcapi->requestParams[$param];
            }
        }

        $tag = array(strtoupper($tag));
        
        $this->_logApiCall($tag);
        
        return $tag;
    }

    /**
     * Retrieve cache key to save data in cache storage
     *
     * @param string $command
     * @param string $args
     * @return string
     */
    protected function _cacheKey($command, $args, $apiKey = null) {

        if (FALSE === in_array($command, $this->_cacheableCommands)) {
            return FALSE;
        }

        if (is_null($args)) {
            $args = array();
        }

        $key = is_null($apiKey) ? $this->_mcapi->api_key : $apiKey;

        return md5($command . serialize($args) . $key);
    }

    /**
     * Log message on <MageMonkey_ApiCall.log> file
     *
     * @param mixed $data
     * @return void
     */
    protected function _logApiCall($data) {
        Mage::helper('monkey')->log($data, 'MageMonkey_ApiCall.log');
    }

}