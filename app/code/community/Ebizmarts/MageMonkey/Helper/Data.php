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
        
        public function getMergeVars($customer, $include_emailaddress){

		$merge_vars             = array();
                $merge_vars_settings    = $this->config('mapping_fields', $customer->getStoreId());
		$maps                   = explode('/n', $merge_vars_settings);
                
		foreach($maps as $map){
			if($map){
				$aux = substr(strstr($map,"customer='"),10);
				$customAtt = (string)substr($aux,0,strpos($aux,"'"));
				$aux = substr(strstr($map,"mailchimp='"),11);
				$chimpTag = (string)substr($aux,0,strpos($aux,"'"));
				if($chimpTag && $customAtt){
					if($customAtt == 'address'){
						$address = $customer->getAddress();
						$merge_vars[strtoupper($chimpTag)] = array('addr1'=>$address['street'],
																   'addr2'=>'',
																   'city'=>$address['city'],
																   'state'=>$address['region'],
																   'zip'=>$address['postcode'],
																   'country'=>$address['country_id']);
					/*****this code has been added thanks to phroggar*****************************/
					}elseif($customAtt == 'date_of_purchase'){
						$orders = Mage::getResourceModel('sales/order_collection')
	                        ->addFieldToFilter('customer_id', $customer->getEntityId())
	                        ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
	                        ->setOrder('created_at', 'desc');
	                    if (($last_order = $orders->getFirstItem()) && (!$last_order->isEmpty())){
	                      $merge_vars[strtoupper($chimpTag)] = Mage::helper('core')->formatDate($last_order->getCreatedAt());
	                    }
                	/*****this code has been added thanks to phroggar*****************************/
					}else{
						if($value = (string)$customer->getData(strtolower($customAtt))) $merge_vars[strtoupper($chimpTag)] = $value;
					}
				}
			}
		}
		if($flag) $merge_vars['EMAIL'] = $customer->getEmail();

		$groups = $customer->getListGroups();
		$groupings = array();
		if(is_array($groups) && count($groups)){
			foreach($groups as $option){
				$parts = explode(']',str_replace('[','',$option));
				if($parts[0] == $customer->getListId() && count($parts) == 5){
					$groupings[] = array('id'=>$parts[2],
									   'name'=>str_replace(',','\,',$parts[1]),
									   'groups'=>str_replace(',','\,',$parts[3]));
				}
			}
		}
		$merge_vars['GROUPINGS'] = $groupings;
		return $merge_vars;
	}
}