<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/17/15
 * Time: 1:08 AM
 * File: PlanController.php
 * Module: magemonkey
 */

class Ebizmarts_Cron_Adminhtml_PlanController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $params = $this->getRequest()->getParams();
        if(isset($params['id'])) {
            $plan = Mage::getModel('ebizmarts_cron/proxy_api')->getPlan($params['id']);
        }
        else {
            $plan = array();
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($plan->plan));
        return;
    }
    public function payAction()
    {
        $params = $this->getRequest()->getParams();
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $data = array('token' => $params['tokenId'], 'plan' => $params['plan'],"base" => $baseUrl);
        $rc = Mage::getModel('ebizmarts_cron/proxy_api')->pay($data);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));
    }
    public function changecardAction()
    {
        $params = $this->getRequest()->getParams();
        $data = array('token' => $params['tokenId'],'plan' => $params['plan'],"customer" => $params['customer']);
        $rc = Mage::getModel('ebizmarts_cron/proxy_api')->changeCard($data);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));

    }
    public function changeplanAction()
    {
        $merchant = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT);
        $params = $this->getRequest()->getParams();
        $data = array('customer'=>$merchant,'plan'=>$params['plan']);
        $rc = Mage::getModel('ebizmarts_cron/proxy_api')->changePlan($data);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));
    }
    public function cancelplanAction()
    {
        $merchant = Mage::getStoreConfig(Ebizmarts_Cron_Model_Config::MERCHANT);
        $rc = Mage::getModel('ebizmarts_cron/proxy_api')->cancelPlan($merchant);
        Mage::log($rc);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));
    }
    public function restoremerchantAction()
    {
        $params = $this->getRequest()->getParams();
        $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $rc = Mage::getModel('ebizmarts_cron/proxy_api')->restoreMerchant($params['merchant'],$baseUrl);
        Mage::log($rc);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rc));
    }
    protected function _isAllowed()
    {
        return true;
    }
}