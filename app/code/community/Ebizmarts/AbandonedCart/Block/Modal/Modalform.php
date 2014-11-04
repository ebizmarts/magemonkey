<?php
/**
 * Created by PhpStorm.
 * User: santisp
 * Date: 23/10/14
 * Time: 02:28 PM
 */
class Ebizmarts_AbandonedCart_Block_Modal_Modalform extends Mage_Adminhtml_Block_Widget_Form {

    protected function _canCancel(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_MODAL, $storeId) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_CAN_CANCEL, $storeId);
    }

    protected function _popupHeading(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_HEADING, $storeId);
    }

    protected function _popupMessage(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_TEXT, $storeId);
    }

    protected function _canShowSubscription(){
        $storeId = Mage::app()->getStore()->getId();
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_SUBSCRIPTION, $storeId);
    }
}