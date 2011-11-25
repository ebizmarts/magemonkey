<?php

/**
 * Mage Monkey helper
 *
 */
class Ebizmarts_MageMonkey_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getWebhooksKey($store, $listId = null)
	{
		if( !is_null($listId) ){
			$store = $this->getStoreByList($listId);
		}

		$crypt = md5((string)Mage::getConfig()->getNode('global/crypt/key'));
		$key   = substr($crypt, 0, (strlen($crypt)/2));

		return ($key . $store);
	}

	public function getUserAgent()
	{
		$modules = Mage::getConfig()->getNode('modules')->children();
		$modulesArray = (array)$modules;

		$aux = (array_key_exists('Enterprise_Enterprise',$modulesArray))? 'EE' : 'CE' ;
		$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
		$version = strpos(Mage::getVersion(),'-')? substr(Mage::getVersion(),0,strpos(Mage::getVersion(),'-')) : Mage::getVersion();
		return (string)'MageMonkey/Mage'.$aux.$version.'/'. $v;
	}

	public function getApiKey($store = null)
	{
		if(is_null($store)){
			$key = $this->config('apikey');
		}else{
			$curstore = Mage::app()->getCurrentStore();
			Mage::app()->setCurrentStore($store);
			$key = $this->config('apikey', $store);
			Mage::app()->setCurrentStore($curstore);
		}

		return $key;
	}

	public function log($data, $filename = 'Monkey.log')
	{
		return Mage::getModel('core/log_adapter', $filename)->log($data);
	}

	public function config($value, $store = null)
	{
		$store = Mage::app()->getStore();

		$configscope = Mage::app()->getRequest()->getParam('store');
		if( $configscope ){
			$store = $configscope;
		}

		return Mage::getStoreConfig("monkey/general/$value", $store);
	}

	public function getDefaultList($storeId)
	{
		$curstore = Mage::app()->getCurrentStore();
		Mage::app()->setCurrentStore($storeId);
			$list = $this->config('list', $storeId);
		Mage::app()->setCurrentStore($curstore);
		return $list;
	}

	public function getStoreByList($mcListId)
	{
        $list = Mage::getModel('core/config_data')->getCollection()
            	->addValueFilter($mcListId)->getFirstItem();

        $store = null;
        if($list->getId() && ($list->getScope() != 'default')){
        	$store = (string)Mage::app()->getStore($list->getScopeId())->getCode();
        }

        return $store;
	}

	public function isWebhookRequest()
	{
		$rq            = Mage::app()->getRequest();
		$monkeyRequest = (string)'monkeywebhookindex';
		$thisRequest   = (string)($rq->getRequestedRouteName() . $rq->getRequestedControllerName() . $rq->getRequestedActionName());

		return (bool)($monkeyRequest === $thisRequest);
	}
}