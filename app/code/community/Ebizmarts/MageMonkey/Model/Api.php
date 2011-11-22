<?php

class Ebizmarts_MageMonkey_Model_Api
{
	protected $_mcapi = null;

	public function __construct($args)
	{
		$apikey = (empty($args) ? Mage::helper('monkey')->getApiKey() : $apikey);
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

			if($args){
				$result = call_user_func_array(array($this->_mcapi, $command), $args);
			}else{
				$result = $this->_mcapi->{$command}();
			}

			return $result;

		}catch(Exception $ex){

			/*if( FALSE !== Mage::helper('adminhtml')->getCurrentUserId() ){
				Mage::getSingleton('adminhtml/session')
					->addError(Mage::helper('monkey')->__($ex->getMessage()));
			}*/
			Mage::logException($ex);

			return $ex->getMessage();

		}
		return FALSE;
	}
}