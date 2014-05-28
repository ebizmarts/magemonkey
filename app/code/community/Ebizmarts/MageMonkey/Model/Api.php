<?php

/**
 * MailChimp API Magento wrapper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
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
     * Cache Helper instance
     *
     * @var Ebizmarts_MageMonkey_Helper_Cache
     * @access protected
     */
    protected $_cacheHelper = null;

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

        $this->_cacheHelper = Mage::helper('monkey/cache');

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

            $cacheKey = $this->_cacheHelper->cacheKey($command, $args, $this->_mcapi->api_key);

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
                $this->_cacheHelper->clearCache($command, $this->_mcapi);

                return (string) $this->_mcapi->errorMessage;
            }

            if ($cacheKey) {
                $cache->saveCacheData(serialize($result), $cacheKey, $this->_cacheHelper->cacheTagForCommand($command, $this->_mcapi));
            }

            //Clear associated cache for this call, for example clear cache for listsForEmail when executing listUnsubscribe
            $this->_cacheHelper->clearCache($command, $this->_mcapi);

            return $result;
        } catch (Exception $ex) {

            Mage::logException($ex);

            return $ex->getMessage();
        }
        return FALSE;
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
