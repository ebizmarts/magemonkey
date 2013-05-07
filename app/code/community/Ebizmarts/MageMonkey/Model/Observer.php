<?php

/**
 * Events Observer model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_Observer
{
	/**
	 * Handle Subscriber object saving process
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function handleSubscriber(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return $observer;
		}

		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();

		if( $subscriber->getBulksync() ){
			return $observer;
		}

		if(Mage::getSingleton('core/session')->getMonkeyCheckout(TRUE)){
			return $observer;
		}

		$email  = $subscriber->getSubscriberEmail();
		if($subscriber->getMcStoreId()){
			$listId = Mage::helper('monkey')->getDefaultList($subscriber->getMcStoreId());
		}
		elseif($subscriber->getStoreId()){
			$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());
		}
		else{
			$listId = Mage::helper('monkey')->getDefaultList(Mage::app()->getStore()->getId());
		}
		$subscriber->setImportMode(TRUE);
		$isConfirmNeed = FALSE;
		if( !Mage::helper('monkey')->isAdmin() &&
			(Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $subscriber->getStoreId()) == 1) ){
			$isConfirmNeed = TRUE;
		}

		if($isConfirmNeed){
       		$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
		}

        //Check if customer is not yet subscribed on MailChimp
		$isOnMailChimp = Mage::helper('monkey')->subscribedToList($email, $listId);

        //Flag only is TRUE when changing to SUBSCRIBE
        if( TRUE === $subscriber->getIsStatusChanged() ){

			if($isOnMailChimp == 1){
				return $observer;
			}

			if($isConfirmNeed){
       			$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED);
       			Mage::getSingleton('core/session')->addSuccess(Mage::helper('monkey')->__('Confirmation request has been sent.'));
 			}

			Mage::getSingleton('monkey/api')->listSubscribe($listId, $email, $this->_mergeVars($subscriber), 'html', $isConfirmNeed);

        }
        // This code unsubscribe users if it's on MailChimp and the status it's unconfirmed
        /*else{
            if(($isOnMailChimp == 1) && ($subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)){
                $rs = Mage::getSingleton('monkey/api')
                                ->listUnsubscribe($listId, $email);
                if($rs !== TRUE){
                    Mage::throwException($rs);
                }
            }
        }*/

	}

	/**
	 * Handle Subscriber deletion from Magento, unsubcribes email from MailChimp
	 * and sends the delete_member flag so the subscriber gets deleted.
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function handleSubscriberDeletion(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		if( TRUE === Mage::helper('monkey')->isWebhookRequest()){
			return $observer;
		}

		$subscriber = $observer->getEvent()->getSubscriber();
		$subscriber->setImportMode(TRUE);

		if( $subscriber->getBulksync() ){
			return $observer;
		}

		$listId = Mage::helper('monkey')->getDefaultList($subscriber->getStoreId());

		Mage::getSingleton('monkey/api', array('store' => $subscriber->getStoreId()))
									->listUnsubscribe($listId, $subscriber->getSubscriberEmail(), TRUE);

	}

	/**
	 * Check for conflicts with rewrite on Core/Email_Template
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function loadConfig(Varien_Event_Observer $observer)
	{
		$action = $observer->getEvent()->getControllerAction();

        //Do nothing for data saving actions
        if ($action->getRequest()->isPost() || $action->getRequest()->getQuery('isAjax')) {
            return $observer;
        }

		if('monkey' !== $action->getRequest()->getParam('section')){
			return $observer;
		}

		$myRewrite = 'Ebizmarts_MageMonkey_Model_Email_Template';
		$modelName = Mage::app()->getConfig()->getModelClassName('core/email_template');

		if(Mage::helper('monkey')->useTransactionalService() && ($myRewrite !== $modelName)){
			Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('monkey')->__('Transactional Emails via MailChimp are not working because of a conflict with: "%s"', $modelName)
            );
		}

		return $observer;
	}

	/**
	 * Handle save of System -> Configuration, section <monkey>
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function saveConfig(Varien_Event_Observer $observer)
	{

		$scope = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $observer->getEvent()->getStore();
		$post   = Mage::app()->getRequest()->getPost();
		$request = Mage::app()->getRequest();

		if( !isset($post['groups']) ){
			return $observer;
		}
		//Check if the api key exist
		if(isset($post['groups']['general']['fields']['apikey']['value'])){
			$apiKey = $post['groups']['general']['fields']['apikey']['value'];
		}else{
			//this case it's when we save the configuration for a particular store
			if((string)$post['groups']['general']['fields']['apikey']['inherit'] == 1){
				$apiKey = Mage::helper('monkey')->getApiKey();
			}
		}

		if(!$apiKey){
			return $observer;
		}

		$selectedLists = array();
		if(isset($post['groups']['general']['fields']['list']['value']))
		{
			$selectedLists []= $post['groups']['general']['fields']['list']['value'];
		}
		else
		{
			if((string)$post['groups']['general']['fields']['list']['inherit'] == 1)
			{
				$selectedLists []= Mage::helper('monkey')->getDefaultList(Mage::app()->getStore()->getId());
			}

		}

		if(!$selectedLists)
		{
			$message = Mage::helper('monkey')->__('There is no List selected please save the configuration again');
			Mage::getSingleton('adminhtml/session')->addWarning($message);
		}

		if(isset($post['groups']['general']['fields']['additional_lists']['value']))
		{
			$additionalLists = $post['groups']['general']['fields']['additional_lists']['value'];
		}
		else
		{
			if((string)$post['groups']['general']['fields']['additional_lists']['inherit'] == 1)
			{
				$additionalLists = Mage::helper('monkey')->getAdditionalList(Mage::app()->getStore()->getId());
			}
		}

		if(is_array($additionalLists)){
			$selectedLists = array_merge($selectedLists, $additionalLists);
		}

		$webhooksKey = Mage::helper('monkey')->getWebhooksKey($scope);

		//Generating Webhooks URL
		$hookUrl = '';
		try{
			switch ($scope) {
		        case 'default':
		            $store = Mage::app()->getDefaultStoreView()->getCode();
		            break;
		        default:
		            $store = $scope;
		            break;
		    }
		    $hookUrl  = Mage::getModel('core/url')->setStore($store)->getUrl(Ebizmarts_MageMonkey_Model_Monkey::WEBHOOKS_PATH, array('wkey' => $webhooksKey));
		}catch(Exception $e){
			$hookUrl  = Mage::getModel('core/url')->getUrl(Ebizmarts_MageMonkey_Model_Monkey::WEBHOOKS_PATH, array('wkey' => $webhooksKey));
		}

		if(FALSE != strstr($hookUrl, '?', true)){
			$hookUrl = strstr($hookUrl, '?', true);
		}

		$api = Mage::getSingleton('monkey/api', array('apikey' => $apiKey));

		//Validate API KEY
		$api->ping();
		if($api->errorCode){
			Mage::getSingleton('adminhtml/session')->addError($api->errorMessage);
			return $observer;
		}

		$lists = $api->lists();

		foreach($lists['data'] as $list){

			if(in_array($list['id'], $selectedLists)){

				 /**
				  * Customer Group - Interest Grouping
				  */
				$magentoGroups = Mage::helper('customer')->getGroups()->toOptionHash();
				array_push($magentoGroups, "NOT LOGGED IN");
				$customerGroup = array('field_type'	=> 'dropdown', 'choices' => $magentoGroups);
				$mergeVars = $api->listMergeVars($list['id']);
				$mergeExist = false;
				foreach($mergeVars as $vars) {
					if($vars['tag'] == 'CGROUP'){
						$mergeExist = true;
						if($magentoGroups === $vars['choices']){
							$update = false;
						}else{
							$update = true;
						}
					}
				}
				if($mergeExist){
					if($update){
						$newValue = array('choices' => $magentoGroups);
						$api->listMergeVarUpdate($list['id'], 'CGROUP', $newValue);
					}
				}else{
					$api->listMergeVarAdd($list['id'], 'CGROUP', 'Customer Groups', $customerGroup);
				}
				 /**
				  * Customer Group - Interest Grouping
				  */

				/**
				 * Adding Webhooks
				 */
				$api->listWebhookAdd($list['id'], $hookUrl);

				//If webhook was not added, add a message on Admin panel
				if($api->errorCode && Mage::helper('monkey')->isAdmin()){

					//Don't show an error if webhook already in, otherwise, show error message and code
					if($api->errorMessage !== "Setting up multiple WebHooks for one URL is not allowed."){
						$message = Mage::helper('monkey')->__('Could not add Webhook "%s" for list "%s", error code %s, %s', $hookUrl, $list['name'], $api->errorCode, $api->errorMessage);
						Mage::getSingleton('adminhtml/session')->addError($message);
					}

				}
				/**
				 * Adding Webhooks
				 */
			}

		}

	}

	/**
	 * Update customer after_save event observer
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void|Varien_Event_Observer
	 */
	public function updateCustomer(Varien_Event_Observer $observer)
	{
		$post = Mage::app()->getRequest()->getPost();
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		$customer = $observer->getEvent()->getCustomer();

		//Handle additional lists subscription on Customer Create Account
		Mage::helper('monkey')->additionalListsSubscription($customer);

		$oldEmail = $customer->getOrigData('email');
		if(!$oldEmail){
			return $observer;
		}

		$mergeVars = $this->_mergeVars($customer, TRUE);
		$api   = Mage::getSingleton('monkey/api', array('store' => $customer->getStoreId()));
		$lists = $api->listsForEmail($oldEmail);
		if(is_array($lists)){
			foreach($lists as $listId){
				$api->listUpdateMember($listId, $oldEmail, $mergeVars);
			}
		}
		$request = Mage::app()->getRequest();
		//Unsubscribe when update customer from admin
		if (!isset($post['subscription']) && $request->getActionName() == 'save' && $request->getControllerName() == 'customer' && $request->getModuleName() == (string)Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName')) {
                 $subscriber = Mage::getModel('newsletter/subscriber')
                               ->loadByEmail($customer->getEmail());
                 $subscriber->setImportMode(TRUE)->unsubscribe();
        }

		return $observer;
	}

	/**
	 * Add flag on session to tell the module if on success page should subscribe customer
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function registerCheckoutSubscribe(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		if(Mage::app()->getRequest()->isPost()){
			$subscribe = Mage::app()->getRequest()->getPost('magemonkey_subscribe');

			Mage::getSingleton('core/session')->setMonkeyPost( serialize(Mage::app()->getRequest()->getPost()) );

			if(!is_null($subscribe)){
				Mage::getSingleton('core/session')->setMonkeyCheckout($subscribe);
			}
		}
	}

	/**
	 * Subscribe customer to Newsletter if flag on session is present
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function registerCheckoutSuccess(Varien_Event_Observer $observer)
	{
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}

		$orderId = (int)current($observer->getEvent()->getOrderIds());
        $order = null;
		if($orderId){
			$order = Mage::getModel('sales/order')->load($orderId);
		}

		if(is_object($order) && $order->getId()){
			//Set Campaign Id if exist
			$campaign_id = Mage::getModel('monkey/ecommerce360')->getCookie()->get('magemonkey_campaign_id');
			if($campaign_id){
				$order->setEbizmartsMagemonkeyCampaignId($campaign_id);
			}
			$sessionFlag = Mage::getSingleton('core/session')->getMonkeyCheckout();
			$forceSubscription = Mage::helper('monkey')->canCheckoutSubscribe();
			if($sessionFlag || $forceSubscription == 3){
				//Guest Checkout
				if( (int)$order->getCustomerGroupId() === Mage_Customer_Model_Group::NOT_LOGGED_IN_ID ){
					Mage::helper('monkey')->registerGuestCustomer($order);
				}

				try{
					$subscriber = Mage::getModel('newsletter/subscriber')
						->setImportMode(TRUE)
						->subscribe($order->getCustomerEmail());
				}catch(Exception $e){
					Mage::logException($e);
				}

			}

			//Multiple lists on checkout
			$monkeyPost = Mage::getSingleton('core/session')->getMonkeyPost(TRUE);
			if($monkeyPost){

				$post = unserialize($monkeyPost);
				$request = new Varien_Object(array('post' => $post));
				$customer  = new Varien_Object(array('email' => $order->getCustomerEmail()));

				//Handle additional lists subscription on Customer Create Account
				Mage::helper('monkey')->additionalListsSubscription($customer, $request);
			}

		}

	}

	/**
	 * Get Mergevars
	 *
	 * @param null|Mage_Customer_Model_Customer $object
	 * @param bool $includeEmail
	 * @return array
	 */
	protected function _mergeVars($object = NULL, $includeEmail = FALSE)
	{
		//Initialize as GUEST customer
		$customer = new Varien_Object;

		$regCustomer   = Mage::registry('current_customer');
		$guestCustomer = Mage::registry('mc_guest_customer');

		if( Mage::helper('customer')->isLoggedIn() ){
			$customer = Mage::helper('customer')->getCustomer();
		}elseif($regCustomer){
			$customer = $regCustomer;
		}elseif($guestCustomer){
			$customer = $guestCustomer;
		}else{
			if(is_null($object)){
				$customer->setEmail($object->getSubscriberEmail())
					 ->setStoreId($object->getStoreId());
			}else{
				$customer = $object;
			}

		}

		if(is_object($object)){
			if($object->getListGroups()){
				$customer->setListGroups($object->getListGroups());
			}

			if($object->getMcListId()){
				$customer->setMcListId($object->getMcListId());
			}
		}

		$mergeVars = Mage::helper('monkey')->getMergeVars($customer, $includeEmail);

		return $mergeVars;
	}

	/** Add mass action option to Sales -> Order grid in admin panel to send orders to MC (Ecommerce360)
	 *
	 * @param Varien_Event_Observer $observer
	 * @return void
	 */
	public function massActionOption($observer)
    {
		if(!Mage::helper('monkey')->canMonkey()){
			return;
		}
        $block = $observer->getEvent()->getBlock();

        if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction) {

            if($block->getRequest()->getControllerName() == 'sales_order') {

                $block->addItem('magemonkey_ecommerce360', array(
                    'label'=> Mage::helper('monkey')->__('Send to MailChimp'),
                    'url'  => Mage::app()->getStore()->getUrl('monkey/adminhtml_ecommerce/masssend', Mage::app()->getStore()->isCurrentlySecure() ? array('_secure'=>true) : array()),
                ));

            }
        }
    }

}
