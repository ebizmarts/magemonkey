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
        Mage::log($storeId, null, 'santiago.log', true);
        return Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_MODAL, $storeId) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_CAN_CANCEL, $storeId);
    }
}