<?php

/**
 * MailChimp API Magento wrapper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Api
{
	/**
	 * Api instance
	 *
	 * @var Ebizmarts_MageMonkey_Model_MCAPI|Ebizmarts_MageMonkey_Model_MCEXPORTAPI
	 * @access protected
	 */
	protected $_mcapi   = null;

	/**
	 * Api host
	 *
	 * @var string
	 * @access protected
	 */
    protected $_apihost = null;

	/**
	 * MC API error code if any
	 *
	 * @var integer
	 * @access public
	 */
    public $errorCode    = null;

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
	public function __construct($args)
	{
		$storeId = isset($args['store']) ? $args['store'] : null;
		$apikey  = (!isset($args['apikey']) ? Mage::helper('monkey')->getApiKey($storeId) : $args['apikey']);

		if( isset($args['_export_']) ){
			$this->_mcapi = new Ebizmarts_MageMonkey_Model_MCEXPORTAPI($apikey);
		}else{
			$this->_mcapi = new Ebizmarts_MageMonkey_Model_MCAPI($apikey);
		}

                //Create actual API URL using API key, borrowed from MCAPI.php
                $dc = "us1";
                if (strstr($this->_mcapi->api_key,"-")){
                    list($key, $dc) = explode("-",$this->_mcapi->api_key,2);
                    if (!$dc) $dc = "us1";
                }
                $this->_apihost = $dc.".".$this->_mcapi->apiUrl["host"];
	}

	/**
	 * Magic __call method
	 *
	 * @link http://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args = null)
	{
		$this->errorCode    = null;
		$this->errorMessage = null;

		return $this->call( $method, $args );
	}

	/**
	 * Perform API call, also can be used "directly"
	 *
	 * @param string $command Command to be performed
	 * @param array $args OPTIONAL call parameters
	 * @return mixed
	 */
	public function call($command, $args)
	{
		try{

			Mage::helper('monkey')->log($this->_apihost, 'MageMonkey_ApiCall.log');
			Mage::helper('monkey')->log($this->_mcapi->api_key, 'MageMonkey_ApiCall.log');
			Mage::helper('monkey')->log($command, 'MageMonkey_ApiCall.log');
			Mage::helper('monkey')->log($args, 'MageMonkey_ApiCall.log');

			if($args){
				$result = call_user_func_array(array($this->_mcapi, $command), $args);
			}else{
				$result = $this->_mcapi->{$command}();
			}

			Mage::helper('monkey')->log($result, 'MageMonkey_ApiCall.log');

			if($this->_mcapi->errorMessage){
				Mage::helper('monkey')->log("Error: {$this->_mcapi->errorMessage}, code {$this->_mcapi->errorCode}", 'MageMonkey_ApiCall.log');

				$this->errorCode    = $this->_mcapi->errorCode;
				$this->errorMessage = $this->_mcapi->errorMessage;

				return (string)$this->_mcapi->errorMessage;
			}

			return $result;

		}catch(Exception $ex){

			Mage::logException($ex);

			return $ex->getMessage();

		}
		return FALSE;
	}
}