<?php

/**
 * MailChimp Customer Account controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Customer_AccountController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        if (!$this->_getCustomerSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

	/**
	 * Display data
	 */
	public function indexAction()
	{
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('Newsletter Subscription'));
        $this->renderLayout();
	}

}
