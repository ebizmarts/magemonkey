<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 4/28/13
 * Time   : 11:20 AM
 * File   : Data.php
 * Module : Ebizmarts_Magemonkey
 */ 
class Ebizmarts_Autoresponder_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getLists()
    {
        $types = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $lists = Mage::getConfig()->getNode('default/ebizmarts_autoresponder')->asArray();
        $lists['abandonedcart'] = array('listname'=>'Abandoned Carts List');
        foreach ($lists as $key =>$data) {
            if(isset($data['listname'])) {
                if(Mage::getStoreConfig("ebizmarts_autoresponder/$key/active",$storeId)||($key=='abandonedcart'&&Mage::getStoreConfig("ebizmarts_abandonedcart/general/active",$storeId))) {
                    $types[$key]['listname'] = (string)$data['listname'];
                    $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
                    $email = $this->_getEmail();
                    $collection->addFieldToFilter('main_table.email',array('eq'=>$email))
                        ->addFieldToFilter('main_table.list',array('eq'=>$key))
                        ->addFieldToFilter('main_table.store_id',array('eq'=>$storeId));
                    if($collection->getSize() > 0) {
                        $types[$key]['checked'] = "";
                    }
                    else {
                        $types[$key]['checked'] = "checked";
                    }
                }
            }
        }
        return $types;
    }
    protected function _getEmail()
    {
        return Mage::helper('customer')->getCustomer()->getEmail();
    }
    // todo: update the counter
    public function generateReviewCoupon($reviewData)
    {
        $store = Mage::app()->getStore()->getId();
        if(!Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_HAS_COUPON,$store)) {
            return false;
        }
        $rc = false;
        // check if is a registered customer if not, return false
        if(!$reviewData->getCustomerId()) {
            return false;
        }
        // if the customer is registered the counter is in the customer account, so load the customer
        $customer = Mage::getModel('customer/customer')->load($$reviewData->getCustomerId());
        $couponTotal = $customer->getEbizmartsReviewsCouponTotal();
        switch(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_COUNTER,$store)) {
            case Ebizmarts_Autoresponder_Model_Config::COUPON_GENERAL:
                // update the counter
                $counter = $customer->getEbizmartsReviewsCounterTotal();
                $counter++;
                $customer->setEbizmartsReviewCounterTotal($counter)->save();
                // check if coupon must be generated
                $generalQuantity = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_GENERAL_QUANTITY,$store);
                switch(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_GENERAL_TYPE)) {
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_EACH:
                        if($counter&&$counter%$generalQuantity) {
                            $rc = true;
                        }
                        break;
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_ONCE:
                        if($counter==$generalQuantity) {
                            $rc = true;
                        }
                        break;
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_SPECIFIC:
                        if($counter&&$counter%$generalQuantity&&$customer->getEbizmartsReviewsCouponTotal()<=Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_SPECIFIC_QUANTITY)) {
                            $rc = true;
                        }
                        break;
                }
                break;
            case Ebizmarts_Autoresponder_Model_Config::COUPON_PER_ORDER:
                // update the counter
                $counter = $reviewData->getCounter();
                $counter++;
                $reviewData->setCounter($counter)->save();
                if($couponTotal >= Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_MAX)) {
                    $rc = false;
                }
                else {
                    if($counter == Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_COUNTER) &&
                        $reviewData->getItems() >= Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_ALMOST)) {
                        $rc = true;
                    }
                }
                break;
        }
        if($rc) { // increase the count of coupons in the customer
            $customer->setEbizmartsReviewsCouponTotal($couponTotal+1)->save();
        }
        return $rc;
    }
}