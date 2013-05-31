<?php

/**
 * Main module helper
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_Mandrill_Helper_Data extends Mage_Core_Helper_Abstract	 {

	private $_configPath = 'mandrill/general/';

	/**
	 * Check if Mandrill is enabled
	 *
	 * @return bool
	 */
	public function useTransactionalService() {
		$active = Mage::getStoreConfigFlag($this->_configPath . "active");
		$key    = $this->getApiKey();

		return ($active && (strlen($key)));
	}

	public function api() {
		return new Mandrill_API();
	}

	/**
	* Retrieves Mandrill API KEY from Magento's configuration
	*
	* @return string
	*/
	public function getApiKey($store = null) {
		return Mage::getStoreConfig($this->_configPath . "apikey",$store);
	}

	/**
	 * Get module User-Agent to use on API requests
	 *
	 * @return string
	 */
	public function getUserAgent() {
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		$aux = (array_key_exists('Enterprise_Enterprise',$modulesArray))? 'EE' : 'CE' ;
		$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_Mandrill/version');
		$version = strpos(Mage::getVersion(),'-')? substr(Mage::getVersion(),0,strpos(Mage::getVersion(),'-')) : Mage::getVersion();
		return (string)'Ebizmarts_Mandrill'.$v.'/Mage'.$aux.$version;
	}

	/**
	 * Logging facility
	 *
	 * @param mixed $data Message to save to file
	 * @param string $filename log filename, default is <Monkey.log>
	 * @return Mage_Core_Model_Log_Adapter
	 */
	public function log($data, $filename = 'Ebizmarts_Mandrill.log') {
		return Mage::getModel('core/log_adapter', $filename)->log($data);
	}

}