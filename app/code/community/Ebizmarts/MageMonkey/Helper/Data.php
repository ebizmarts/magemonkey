<?php

/**
 * Mage Monkey helper
 *
 */
class Ebizmarts_MageMonkey_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getUserAgent()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		$aux = (array_key_exists('Enterprise_Enterprise',$modulesArray))? 'EE' : 'CE' ;
		$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
		$version = strpos(Mage::getVersion(),'-')? substr(Mage::getVersion(),0,strpos(Mage::getVersion(),'-')) : Mage::getVersion();
		return (string)'Ebizmarts/Mage'.$aux.$version.'/'. $v;
	}

	public function getApiKey()
	{
		return $this->config('apikey');
	}

	public function log($data)
	{
		return Mage::getModel('core/log_adapter', 'Monkey.log')->log($data);
	}

	public function config($value, $store = null)
	{
		$store = Mage::app()->getStore();

		return Mage::getStoreConfig("monkey/general/$value", $store);
	}
}