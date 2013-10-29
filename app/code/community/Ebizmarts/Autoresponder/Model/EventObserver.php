<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/24/13
 * Time   : 5:27 PM
 * File   : EventObserver.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_EventObserver
{
    /**
     * @param Varien_Event_Observer $o
     */
    public function saveConfig(Varien_Event_Observer $o)
    {
        if(Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        }
        elseif(Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        }
        else {
            $scope = "default";
        }
        $store  = is_null($o->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $o->getEvent()->getStore();
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::helper('mandrill')->useTransactionalService()) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show')) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(!Mage::getStoreConfig('customer/address/dob_show',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Wishlist',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
        if(Mage::getStoreConfig('advanced/modules_disable_output/Mage_Review',$store)) {
            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_ACTIVE,false,$scope,$store);
            Mage::getConfig()->cleanCache();
        }
    }
    public function actionAfter(Varien_Event_Observer $o)
    {
        if($o->getEvent()->getControllerAction()->getFullActionName() == 'review_product_post') {
            Mage::dispatchEvent("review_product_post_after", array('request' => $o->getControllerAction()->getRequest()));
        }
    }
    public function reviewProductPostAfter(Varien_Event_Observer $o)
    {
        $params = Mage::app()->getRequest()->getParams();
        $storeId = Mage::app()->getStore()->getId();
        if(isset($params['token'])) {
            $token = $params['token'];
            $reviewData = Mage::getModel('ebizmarts_autoresponder/review')->loadByToken($token);
            if(Mage::helper('ebizmarts_autoresponder')->generateReviewCoupon($reviewData)) {
                //generate coupon
                list($couponcode,$discount,$toDate) = $this->_createNewCoupon($storeId,$email);
            }
        }
    }
    protected function _createNewCoupon($store,$email)
    {
        $couponamount = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_DISCOUNT, $store);
        $couponexpiredays = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_EXPIRE, $store);
        $coupontype = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_DISCOUNT_TYPE, $store);
        $couponlength = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_LENGTH, $store);
        $couponlabel = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_LABEL, $store);
        $websiteid =  Mage::getModel('core/store')->load($store)->getWebsiteId();

        $fromDate = date("Y-m-d");
        $toDate = date('Y-m-d', strtotime($fromDate. " + $couponexpiredays day"));
        if($coupontype == 1) {
            $action = 'cart_fixed';
            $discount = Mage::app()->getStore($store)->getCurrentCurrencyCode()."$couponamount";
        }
        elseif($coupontype == 2) {
            $action = 'by_percent';
            $discount = "$couponamount%";
        }
        $customer_group = new Mage_Customer_Model_Group();
        $allGroups  = $customer_group->getCollection()->toOptionHash();
        $groups = array();
        foreach($allGroups as $groupid=>$name) {
            $groups[] = $groupid;
        }
        $coupon_rule = Mage::getModel('salesrule/rule');
        $coupon_rule->setName("Review coupon $email")
            ->setDescription("Review coupon $email")
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setIsActive(1)
            ->setCouponType(2)
            ->setUsesPerCoupon(1)
            ->setUsesPerCustomer(1)
            ->setCustomerGroupIds($groups)
            ->setProductIds('')
            ->setLengthMin($couponlength)
            ->setLengthMax($couponlength)
            ->setSortOrder(0)
            ->setStoreLabels(array($couponlabel))
            ->setSimpleAction($action)
            ->setDiscountAmount($couponamount)
            ->setDiscountQty(0)
            ->setDiscountStep('0')
            ->setSimpleFreeShipping('0')
            ->setApplyToShipping('0')
            ->setIsRss(0)
            ->setWebsiteIds($websiteid);
        $uniqueId = Mage::getSingleton('salesrule/coupon_codegenerator', array('length' => $couponlength))->generateCode();
        $coupon_rule->setCouponCode($uniqueId);
        $coupon_rule->save();
        return array($uniqueId,$discount,$toDate);
    }
}