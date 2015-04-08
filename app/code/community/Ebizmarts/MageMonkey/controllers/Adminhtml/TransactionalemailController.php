<?php

/**
 * Transactional Email Service manager controller
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
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
     * Mandrill verified emails grid
     */
    public function mandrillAction()
    {
        $this->_initAction();
        $this->_title($this->__('Mandrill'));
        $this->renderLayout();
    }

    /**
     * STS verified emails grid
     */
    public function stsAction()
    {
        $this->_initAction();
        $this->_title($this->__('Amazon Simple Email Service'));
        $this->renderLayout();
    }

    /**
     * Delete valid email address from Amazon SES
     */
    public function newAction()
    {
        $this->_initAction();
        $this->_title($this->__('Validate Email'));
        $this->renderLayout();
    }

    public function validateEmailAction()
    {
        $service = $this->getRequest()->getPost('service', 'sts');

        if ($this->getRequest()->isPost() && $service) {

            $store = $this->getRequest()->getPost('store');

            $apiKey = Mage::helper('monkey')->getApiKey($store);
            if ($service == 'mandrill') {
                $apiKey = Mage::helper('monkey')->getMandrillApiKey($store);
            }

            $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory($service)
                ->setApiKey($apiKey);

            $mail->verifyEmailAddress($this->getRequest()->getPost('email_address'));
            if ($mail->errorCode) {
                $this->_getSession()->addError($this->__($mail->errorMessage));
            } else {
                $this->_getSession()->addSuccess($this->__('Email address verified.'));
            }
        }

        $this->_redirect('monkey/adminhtml_transactionalemail/' . $service);
    }

    /**
     * Delete valid email address from Mandrill
     */
    public function mandrillDisableAction()
    {
        $email = $this->getRequest()->getParam('email');
        $store = $this->getRequest()->getParam('store', 0);

        if ($email) {
            $apiKey = Mage::helper('monkey')->getMandrillApiKey($store);
            $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory('mandrill')
                ->setApiKey($apiKey);

            $mail->usersDisableSender($email);
            if ($mail->errorCode) {
                $this->_getSession()->addError($this->__($mail->errorMessage));
            } else {
                $this->_getSession()->addSuccess($this->__('Email address was disabled.'));
            }
        }

        $this->_redirect('monkey/adminhtml_transactionalemail/mandrill');
    }

    /**
     * Delete valid email address from Amazon SES
     */
    public function stsDeleteAction()
    {
        $email = $this->getRequest()->getParam('email');
        $store = $this->getRequest()->getParam('store', 0);

        if ($email) {
            $apiKey = Mage::helper('monkey')->getApiKey($store);
            $mail = Ebizmarts_MageMonkey_Model_TransactionalEmail_Adapter::factory('sts')
                ->setApiKey($apiKey);

            $mail->deleteVerifiedEmailAddress($email);
            if ($mail->errorCode) {
                $this->_getSession()->addError($this->__($mail->errorMessage));
            } else {
                $this->_getSession()->addSuccess($this->__('Email address deleted.'));
            }
        }

        $this->_redirect('monkey/adminhtml_transactionalemail/sts');
    }

}
