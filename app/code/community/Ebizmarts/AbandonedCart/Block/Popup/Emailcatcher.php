<?php

/**
 * Created by PhpStorm.
 * User: santisp
 * Date: 23/10/14
 * Time: 02:28 PM
 */
class Ebizmarts_AbandonedCart_Block_Popup_Emailcatcher extends Mage_Core_Block_Template
{

    protected function _canCancel()
    {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, $storeId) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_CAN_CANCEL, $storeId);
    }

    protected function _popupHeading()
    {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_HEADING, $storeId);
    }

    protected function _popupMessage()
    {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_TEXT, $storeId);
    }

    protected function _modalSubscribe()
    {
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_SUBSCRIPTION, $storeId);
    }

    protected function _createCoupon($cookie)
    {
        $storeId = Mage::app()->getStore()->getId();
        if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_CREATE_COUPON, $storeId)) {
            $cookieValues = explode('/', $cookie);
            $email = $cookieValues[0];
            $email = str_replace(' ', '+', $email);
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_CREATE_COUPON, $storeId)) {
                $collection = Mage::getModel('ebizmarts_abandonedcart/popup')->getCollection()
                    ->addFieldToFilter('email', array('eq' => $email));
                if (!count($collection)) {
                    $addEmail = Mage::getModel('ebizmarts_abandonedcart/popup');
                    $addEmail->setEmail($email)
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->save();
                }
            }
        }
    }

    protected function _getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    protected function _handleCookie(){
        $storeId = Mage::app()->getStore()->getId();
        $emailCookie = Mage::getModel('core/cookie')->get('email');
        $subscribeCookie = Mage::getModel('core/cookie')->get('subscribe');
        $cookieValues = explode('/', $emailCookie);
        $email = $cookieValues[0];
        $email = str_replace(' ', '+', $email);
        $fName = $cookieValues[1];
        $lName = $cookieValues[2];
        if($subscribeCookie == 'true'){
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if(!$subscriber->getId()) {
                $subscriber = Mage::getModel('newsletter/subscriber')
                    ->setStoreId($storeId);
                if($fName){
                    $subscriber->setSubscriberFirstname($fName);
                }
                if($lName){
                    $subscriber->setSubscriberLastname($lName);
                }
                $subscriber->subscribe($email);
                return 'location.reload';
            }
        }
    }
}