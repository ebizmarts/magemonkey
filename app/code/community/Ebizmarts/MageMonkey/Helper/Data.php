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
			$store = $this->getStoreByList($listId, TRUE);
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
		return (string)'MageMonkey'.$v.'/Mage'.$aux.$version;
	}

	public function getApiKey($store = null)
	{
		if(is_null($store)){
			$key = $this->config('apikey');
		}else{
			$curstore = Mage::app()->getStore();
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

	public function canCheckoutSubscribe()
	{
		return Mage::getStoreConfigFlag('monkey/general/checkout_subscribe');
	}

	public function getDefaultList($storeId)
	{
		$curstore = Mage::app()->getStore();
		Mage::app()->setCurrentStore($storeId);
			$list = $this->config('list', $storeId);
		Mage::app()->setCurrentStore($curstore);
		return $list;
	}

	public function getStoreByList($mcListId, $includeDefault = FALSE)
	{
        $list = Mage::getModel('core/config_data')->getCollection()
            	->addValueFilter($mcListId)->getFirstItem();

        $store = null;
        if($list->getId()){

        	$isDefault = (bool)($list->getScope() == 'default');
        	if(!$isDefault && !$includeDefault){
        		$store = (string)Mage::app()->getStore($list->getScopeId())->getCode();
        	}else{
        		$store = $list->getScope();
        	}

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


	public function getMergeVars($customer, $includeEmail = FALSE)
	{
		$merge_vars = array();
        $maps       = unserialize( $this->config('map_fields', $customer->getStoreId()) );

		if(!$maps){
			return;
		}

		$request = Mage::app()->getRequest();

		foreach($maps as $map){

			$customAtt = $map['magento'];
			$chimpTag  = $map['mailchimp'];

			if($chimpTag && $customAtt){

				$key = strtoupper($chimpTag);

				switch ($customAtt) {
					case 'gender':
							$val = (int)$customer->getData(strtolower($customAtt));
							if($val == 1){
								$merge_vars[$key] = 'Male';
							}elseif($val == 2){
								$merge_vars[$key] = 'Female';
							}
						break;
					case 'dob':
							$dob = (string)$customer->getData(strtolower($customAtt));
							if($dob){
								$merge_vars[$key] = (substr($dob, 5, 2) . '/' . substr($dob, 8, 2));
							}
						break;
					case 'billing_address':
					case 'shipping_address':

						$addr = explode('_', $customAtt);
						$address = $customer->getPrimaryAddress('default_' . $addr[0]);
						if($address){
							$merge_vars[$key] = array(
																	'addr1'   => $address->getStreet(1),
														   			'addr2'   => $address->getStreet(2),
															   		'city'    => $address->getCity(),
															   		'state'   => $address->getRegion(),
															   		'zip'     => $address->getPostcode(),
															   		'country' => $address->getCountryId()
															   	  );
						}

						break;
					case 'date_of_purchase':

						$last_order = Mage::getResourceModel('sales/order_collection')
                        	->addFieldToFilter('customer_id', $customer->getId())
                        	->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                        	->setOrder('created_at', 'desc')
                        	->getFirstItem();
	                    if ( $last_order->getId() ){
	                    	$merge_vars[$key] = Mage::helper('core')->formatDate($last_order->getCreatedAt());
	                    }

						break;
					case 'ee_customer_balance':
						//TODO
						break;
					default:

						if( ($value = (string)$customer->getData(strtolower($customAtt)))
							OR ($value = (string)$request->getPost(strtolower($customAtt))) ){
							$merge_vars[$key] = $value;
						}

						break;
				}

			}
		}

		//GUEST
		if( !$customer->getId() ){
			$guestFirstName = $this->config('guest_name', $customer->getStoreId());
			$guestLastName  = $this->config('guest_lastname', $customer->getStoreId());

			if($guestFirstName){
				$merge_vars['FNAME'] = $guestFirstName;
			}
			if($guestLastName){
				$merge_vars['LNAME'] = $guestLastName;
			}
		}
		//GUEST

		if($includeEmail){
			$merge_vars['EMAIL'] = $customer->getEmail();
		}

		/*

		=== TODO ===

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
		$merge_vars['GROUPINGS'] = $groupings;*/

		return $merge_vars;
	}

}