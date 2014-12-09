<?php
/**
 * Created by PhpStorm.
 * User: santisp
 * Date: 23/10/14
 * Time: 02:28 PM
 */
class Ebizmarts_AbandonedCart_Block_Popup_Emailcatcher extends Mage_Adminhtml_Block_Widget_Form {

    protected function _canCancel(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, $storeId) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_CAN_CANCEL, $storeId);
    }

    protected function _popupHeading(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_HEADING, $storeId);
    }

    protected function _popupMessage(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_TEXT, $storeId);
    }

    protected function _modalSubscribe(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_SUBSCRIPTION, $storeId);
    }

    protected function _getStoreId(){
        return Mage::app()->getStore()->getId();
    }
}