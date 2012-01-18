<?php

/**
 * Transactional Email Service manager controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Adminhtml_TransactionalemailController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->_title($this->__('Newsletter'))
             ->_title($this->__('MailChimp'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter/magemonkey');
        return $this;
    }

	/**
	 * STS grid
	 */
	public function stsAction()
	{
		$this->_initAction();
		$this->_title($this->__('Amazon Simple Email Service'));
        $this->renderLayout();
	}

	/**
	 * Just the import grid for AJAX calls
	 */
	public function stsgridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkey/adminhtml_bulksync_queueImport_grid')->toHtml()
        );
	}

	/**
	 * Delete valid email address from Amazon SES
	 */
	public function stsDeleteAction()
	{
		$email = $this->getRequest()->getParam('email');
		$store = $this->getRequest()->getParam('store', 0);

		if($email){
			$apiKey  = Mage::helper('monkey')->getApiKey($store);
			$mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory('sts')
						->setApiKey($apiKey);

            $mail->deleteVerifiedEmailAddress($email);
            if($mail->errorCode){
				$this->_getSession()->addError($this->__($mail->errorCode));
			}else{
				$this->_getSession()->addSuccess($this->__('Email address deleted.'));
			}
		}

		$this->_redirect('monkey/adminhtml_transactionalemail/sts');
	}

}
