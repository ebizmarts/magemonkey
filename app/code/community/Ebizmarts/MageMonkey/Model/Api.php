<?php

class Ebizmarts_MageMonkey_Model_Api
{
	protected $_mcapi = null;

	public function __construct($args)
	{
		$storeId = isset($args['store']) ? $args['store'] : null;
		$apikey  = (!isset($args['apikey']) ? Mage::helper('monkey')->getApiKey($storeId) : $apikey);
		$this->_mcapi = new Ebizmarts_MageMonkey_Model_MCAPI($apikey);
	}

	public function __call($method, $args = null)
	{
		return $this->call( $method, $args );
	}

	/**
	 * Perform API call, also can be used "manually"
	 *
	 *@param string $command Command to be performed
	 *@param optional array $args Call parameters
	 */
	public function call($command, $args)
	{
		try{

			Mage::helper('monkey')->log($command, 'MageMonkey_ApiCall.log');
			Mage::helper('monkey')->log($args, 'MageMonkey_ApiCall.log');
			Mage::helper('monkey')->log($this->_mcapi->apiUrl, 'MageMonkey_ApiCall.log');

			if($args){
				$result = call_user_func_array(array($this->_mcapi, $command), $args);
			}else{
				$result = $this->_mcapi->{$command}();
			}

			Mage::helper('monkey')->log($result, 'MageMonkey_ApiCall.log');

			return $result;

		}catch(Exception $ex){

			Mage::logException($ex);

			return $ex->getMessage();

		}
		return FALSE;
	}
}