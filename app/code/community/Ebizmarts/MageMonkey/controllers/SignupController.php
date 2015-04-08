<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_SignupController extends Mage_Core_Controller_Front_Action
{

    /**
     * Perform saving operation, update grouping and subscribe/unsubscribe operations
     */
    public function saveadditionalAction()
    {
        if ($this->getRequest()->isPost()) {

            $loggedIn = Mage::helper('customer')->isLoggedIn();
            $guestEmail = $this->getRequest()->getPost('monkey_email');

            if (!$loggedIn && !Zend_Validate::is($guestEmail, 'EmailAddress')) {
                Mage::getSingleton('core/session')
                    ->addError($this->__('Please specify a valid email address.'));
                $this->_redirect($this->_getRedirectPath());
                return;
            }
            Mage::helper('monkey')->handlePost($this->getRequest(), $guestEmail);

        }

        $this->_redirect($this->_getRedirectPath());
    }

    protected function _getRedirectPath()
    {
        $path = '/';

        if (Mage::helper('customer')->isLoggedIn()) {
            $path = 'monkey/customer_account/index';
        }

        return $path;
    }

}
