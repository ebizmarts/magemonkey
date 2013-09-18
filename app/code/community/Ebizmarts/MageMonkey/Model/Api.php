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

	public $api_key;

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
		$this->api_key = $apikey;

        $this->_mcapi = new Mailchimp($apikey);

        $this->_cacheHelper = Mage::helper('monkey/cache');

        //Create actual API URL using API key, borrowed from MCAPI.php
        $dc = "us1";
        if (strstr($this->_mcapi->apikey, "-")) {
            list($key, $dc) = explode("-", $this->_mcapi->apikey, 2);
            if (!$dc)
                $dc = "us1";
        }
        $this->_apihost = $dc . "." . $this->_mcapi->root;
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
    public function call($command, $args = null) {
        try {
			$cacheKey = $this->_cacheHelper->cacheKey($command, $args, $this->_mcapi->apikey);

            $this->_logApiCall($command);
			$this->_logApiCall($args);

			$args['apikey'] = $args;

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

            $result = $this->_mcapi->call($command, $args);
            $this->_logApiCall($result);

            if ($cacheKey) {
                $cache->saveCacheData(serialize($result), $cacheKey, $this->_cacheHelper->cacheTagForCommand($command, $args));
            }

            //Clear associated cache for this call, for example clear cache for helper/lists-for-email when executing lists/unsubscribe
            $this->_cacheHelper->clearCache($command, $args);

			return $result;
        } catch (Exception $ex) {
        	$this->_logApiCall($ex->getMessage());
            return $ex->getMessage();
        }
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
