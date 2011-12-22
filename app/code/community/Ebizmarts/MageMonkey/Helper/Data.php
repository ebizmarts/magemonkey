<?php

/**
 * Mage Monkey helper
 *
 */
class Ebizmarts_MageMonkey_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function isAdmin()
	{
		return Mage::getSingleton('admin/session')->isLoggedIn();
	}

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
		$store = is_null($store) ? Mage::app()->getStore() : $store;

		$configscope = Mage::app()->getRequest()->getParam('store');
		if( $configscope ){
			$store = $configscope;
		}

		return Mage::getStoreConfig("monkey/general/$value", $store);
	}

	public function canCheckoutSubscribe()
	{
		return (bool)($this->config('checkout_subscribe') != 0);
	}

	public function ecommerce360Active()
	{
		return (bool)($this->config('ecommerce360') != 0);
	}

	public function canMonkey()
	{
		return (bool)((int)$this->config('active') !== 0);
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

	public function getMergeMaps($storeId)
	{
		return unserialize( $this->config('map_fields', $storeId) );
	}

	public function getMergeVars($customer, $includeEmail = FALSE, $websiteId = NULL)
	{
		$merge_vars   = array();
        $maps         = $this->getMergeMaps($customer->getStoreId());

		if(!$maps){
			return;
		}

		$request = Mage::app()->getRequest();

		//Add Customer data to Subscriber if is Newsletter_Subscriber is Customer
		if($customer->getCustomerId()){
			$customer->addData(Mage::getModel('customer/customer')->load($customer->getCustomerId())
									->toArray());
		}

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
						$address = $customer->{'getPrimary'.ucfirst($addr[0]).'Address'}();
						if($address){
							$merge_vars[$key] = array(
																	'addr1'   => $address->getStreet(1),
														   			'addr2'   => $address->getStreet(2),
															   		'city'    => $address->getCity(),
															   		'state'   => (!$address->getRegion() ? $address->getCity() : $address->getRegion()),
															   		'zip'     => $address->getPostcode(),
															   		'country' => $address->getCountryId()
															   	  );
						}

						break;
					case 'date_of_purchase':

						$last_order = Mage::getResourceModel('sales/order_collection')
                        	->addFieldToFilter('customer_email', $customer->getEmail())
                        	->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
                        	->setOrder('created_at', 'desc')
                        	->getFirstItem();
	                    if ( $last_order->getId() ){
	                    	$merge_vars[$key] = Mage::helper('core')->formatDate($last_order->getCreatedAt());
	                    }

						break;
					case 'ee_customer_balance':
						//TODO

						/*$websiteBalance = Mage::getModel('enterprise_customerbalance/balance')
	                    					->setCustomerId($customer->getId())
	                    					->setWebsiteId($websiteId)
	                    					->load()
	                    					->getAmount();*/


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

	/**
	 * Register on registry GUEST customer data for MergeVars for on checkout subscribe
	 */
	public function registerGuestCustomer($order)
	{

		if( Mage::registry('mc_guest_customer') ){
			return;
		}

		$customer = new Varien_Object;

		$customer->setId(time());
		$customer->setEmail($order->getBillingAddress()->getEmail());
		$customer->setStoreId($order->getStoreId());
		$customer->setFirstname($order->getBillingAddress()->getFirstname());
		$customer->setLastname($order->getBillingAddress()->getLastname());
		$customer->setPrimaryBillingAddress($order->getBillingAddress());
		$customer->setPrimaryShippingAddress($order->getShippingAddress());

		Mage::register('mc_guest_customer', $customer, TRUE);

	}

	public function createCustomerAccount($accountData, $websiteId)
	{
		$customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);

		//$accountData ['is_subscribed'] = 1;

		if(!isset($accountData['firstname']) OR empty($accountData['firstname'])){
			$accountData['firstname'] = $this->__('Store');
		}
		if(!isset($accountData['lastname']) OR empty($accountData['lastname'])){
			$accountData['lastname'] = $this->__('Guest');
		}

		$customerForm = Mage::getModel('customer/form');
    	$customerForm->setFormCode('customer_account_create')
        	->setEntity($customer)
        	->initDefaultValues();
        // emulate request
        $request = $customerForm->prepareRequest($accountData);
        $customerData    = $customerForm->extractData($request);
        $customerForm->restoreData($customerData);

		$customerErrors = $customerForm->validateData($customerData);

		if($customerErrors){
            $customerForm->compactData($customerData);

            $pwd = $customer->generatePassword(8);
            $customer->setPassword($pwd);
            $customer->setConfirmation($pwd);


			/**
			 * Handle Address related Data
			 */
			$billing = $shipping = null;
			if(isset($accountData['billing_address']) && !empty($accountData['billing_address'])){
				$this->_McAddressToMage($accountData, 'billing', $customer);
			}
			if(isset($accountData['shipping_address']) && !empty($accountData['shipping_address'])){
				$this->_McAddressToMage($accountData, 'shipping', $customer);
			}
			/**
			 * Handle Address related Data
			 */

            $customerErrors = $customer->validate();
            if (is_array($customerErrors) && count($customerErrors)) {

                //TODO: Do something with errors.

            }else{
            	$customer->save();

				if ( $customer->isConfirmationRequired() ){
                    $customer->sendNewAccountEmail('confirmation');
				}
				//$customer->sendPasswordReminderEmail();
            }
		}

		return $customer;
	}

	protected function _McAddressToMage(array $data, $type, $customer)
	{
		$addressData = $data["{$type}_address"];
		$address = explode(str_repeat(chr(32), 2), $addressData);
		list($addr1, $addr2, $city, $state, $zip, $country) = $address;

		$region = Mage::getModel('directory/region')->loadByName($state, $country);

		$mgAddress = array(
							'firstname' => $data['firstname'],
							'lastname' => $data['lastname'],
							'street'  => array($addr1, $addr2),
							'city' => $city,
							'country_id' => $country,
							'region' => $state,
							'region_id' => (!is_null($region->getId()) ? $region->getId() : null),
							'postcode' => $zip,
							'telephone' => 'not_provided',
						  );

        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address');
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_register_address')
            ->setEntity($address);

		$addrrequest = $addressForm->prepareRequest($mgAddress);
        $addressData = $addressForm->extractData($addrrequest);
        $addressErrors  = $addressForm->validateData($addressData);

        $errors = array();
        if ($addressErrors === true) {
            $address->setId(null)
            	->setData("is_default_{$type}", TRUE);
            $addressForm->compactData($addressData);
            $customer->addAddress($address);

            $addressErrors = $address->validate();
            if (is_array($addressErrors)) {
                $errors = array_merge($errors, $addressErrors);
            }
        } else {
            $errors = array_merge($errors, $addressErrors);
        }

		return $errors;
	}

}