<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/16/15
 * Time: 5:09 PM
 * File: StripeOauth.php
 * Module: magemonkey
 */

class Ebizmarts_Cron_Block_Adminhtml_System_Config_StripeWizard extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_customer=null;
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('ebizmarts/cron/system/config/stripe_wizard.phtml');
        }
        return $this;
    }
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();

        //$label = $originalData['button_label'];

        //Check if api key works
//        $ping = Mage::getModel('monkey/api');
//        $ping->ping();
//        if (!$ping->errorCode) {
//            $label = "Change API credentials";
//        }

//        $this->addData(array(
//            'button_label' => $this->helper('ebizmarts_cron')->__($label),
//            'button_url' => $this->helper('monkey/oauth2')->authorizeRequestUrl(),
//            'html_id' => $element->getHtmlId(),
//        ));
        return $this->_toHtml();
    }
    public function getName()
    {
        return Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::NAME);
    }
    public function getPk()
    {
        return Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::PK);
    }
    public function getImage()
    {
        return Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::IMAGE);
    }
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan');
    }
    public function getPostUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan/pay');
    }
    public function getMerchant()
    {
        return Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT);
    }
    public function getUnpayed()
    {
        $merchant = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT);
        if($merchant) {
            $this->_customer = Mage::getModel('ebizmarts_cron/proxy_api')->getCustomer($merchant);
            $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            if(isset($this->_customer->metadata->baseUrl)&&$baseUrl!=$this->_customer->metadata->baseUrl){
                return Ebizmarts_Cron_Model_Config::WRONG_URL;
            }
            if(isset($this->_customer->subscriptions->data[0])) {
                $status = $this->_customer->subscriptions->data[0]->status;
                switch ($status) {
                    case 'active':
                    case 'trialing':
                        $rc = Ebizmarts_Cron_Model_Config::ALL_OK;
                        break;
                    case 'past_due':
                    case 'canceled':
                        $rc = Ebizmarts_Cron_Model_Config::UNPAYED;
                        break;
                }
            }
            else {
                $rc = Ebizmarts_Cron_Model_Config::NO_SUBSCRIPTION;
            }
        }
        else {
            $rc = Ebizmarts_Cron_Model_Config::NO_MERCHANT;
        }
        return $rc;
    }
    public function getChangeCardUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan/changecard');
    }
    public function getLast4()
    {
        if(!$this->_customer)
        {
            $this->_customer = Mage::getModel('ebizmarts_cron/proxy_api')->getCustomer(Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT));
        }
        return $this->_customer->sources->data[0]->last4;
    }
    public function getBrand()
    {
        if(!$this->_customer)
        {
            $this->_customer = Mage::getModel('ebizmarts_cron/proxy_api')->getCustomer(Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT));
        }
        return $this->_customer->sources->data[0]->brand;
    }
    public function getEmail()
    {
        if(!$this->_customer)
        {
            $this->_customer = Mage::getModel('ebizmarts_cron/proxy_api')->getCustomer(Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT));
        }
        return $this->_customer->sources->data[0]->name;
    }
    public function getChangePlanUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan/changeplan');
    }
    public function getCancelPlanUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan/cancelplan');
    }
    public function getRestoreMechantUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/plan/restoremerchant');
    }
}