<?php

class Ebizmarts_MageMonkey_Model_Api
{
	protected $_mcapi   = null;
    protected $_apihost = null;

    public $errorCode    = null;
    public $errorMessage = null;

	public function __construct($args)
	{
		$storeId = isset($args['store']) ? $args['store'] : null;
		$apikey  = (!isset($args['apikey']) ? Mage::helper('monkey')->getApiKey($storeId) : $args['apikey']);
		$this->_mcapi = new Ebizmarts_MageMonkey_Model_MCAPI($apikey);

                //Create actual API URL using API key, borrowed from MCAPI.php
                $dc = "us1";
                if (strstr($this->_mcapi->api_key,"-")){
                    list($key, $dc) = explode("-",$this->_mcapi->api_key,2);
                    if (!$dc) $dc = "us1";
                }
                $this->_apihost = $dc.".".$this->_mcapi->apiUrl["host"];
	}

	public function __call($method, $args = null)
	{
		$this->errorCode    = null;
		$this->errorMessage = null;

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