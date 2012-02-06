<?php

class Ebizmarts_MageMonkey_SignupController extends Mage_Core_Controller_Front_Action
{

	/**
	 * Perform saving operation, update grouping and subscribe/unsubscribe operations
	 */
	public function saveadditionalAction()
	{
		if($this->getRequest()->isPost()){

			$loggedIn = Mage::helper('customer')->isLoggedIn();
			if(!$loggedIn && !Zend_Validate::is($email, 'EmailAddress')){
				Mage::getSingleton('core/session')
					->addError($this->__('Please specify a valid email address.'));
				$this->_redirect($this->_getRedirectPath());
				return;
			}

			Mage::helper('monkey')->handlePost($this->getRequest(), $this->getRequest()->getPost('monkey_email'));
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
