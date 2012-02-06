<?php

class Ebizmarts_MageMonkey_SignupController extends Mage_Core_Controller_Front_Action
{

	/**
	 * Perform saving operation, update grouping and subscribe/unsubscribe operations
	 */
	public function saveadditionalAction()
	{
		if($this->getRequest()->isPost()){

			$guestEmail = $this->getRequest()->getPost('monkey_email');

			//<state> param is an html serialized field containing the default form state
			//before submission, we need to parse it as a request in order to save it to $odata and process it
			parse_str($this->getRequest()->getPost('state'), $odata);

			$curlists = (TRUE === array_key_exists('list', $odata)) ? $odata['list'] : array();
			$lists    = $this->getRequest()->getPost('list', array());

			$api       = Mage::getSingleton('monkey/api');
			$customer  = Mage::helper('customer')->getCustomer();
			$email     =  $guestEmail ? $guestEmail : $customer->getEmail();

			$loggedIn = Mage::helper('customer')->isLoggedIn();
			if(!$loggedIn && !Zend_Validate::is($email, 'EmailAddress')){
				Mage::getSingleton('core/session')
					->addError($this->__('Please specify a valid email address.'));
				$this->_redirect($this->_getRedirectPath());
				return;
			}



			if( !empty($curlists) ){

				//Handle Unsubscribe and groups update actions
				foreach($curlists as $listId => $list){

					if(FALSE === array_key_exists($listId, $lists)){

						//Unsubscribe Email
						$api->listUnsubscribe($listId, $email);

					}else{

						$groupings = $lists[$listId];
						unset($groupings['subscribed']);
						$customer->setMcListId($listId);
						$customer->setListGroups($groupings);
						$mergeVars = Mage::helper('monkey')->getMergeVars($customer);

						//Handle groups update
						$api->listUpdateMember($listId, $email, $mergeVars);

					}

				}

			}

			//Subscribe to new lists
			$subscribe = array_diff_key($lists, $curlists);
			if( !empty($subscribe) ){

				foreach($subscribe as $listId => $slist){

					$groupings = $lists[$listId];
					unset($groupings['subscribed']);
					$customer->setListGroups($groupings);
					$customer->setMcListId($listId);
					$mergeVars = Mage::helper('monkey')->getMergeVars($customer);

					$api->listSubscribe($listId, $email, $mergeVars, 'html', ($loggedIn ? false : true));

				}

			}

		}

		$this->_redirect($this->_getRedirectPath());
	}

	protected function _getRedirectPath()
	{
		$path = '/';

		if(Mage::helper('customer')->isLoggedIn()){
			$path = '*/*/index';
		}

		return $path;
	}

}
