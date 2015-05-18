<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_EventObserver
{
    /**
     * @param Varien_Event_Observer $o
     */
    public function saveConfig(Varien_Event_Observer $o)
    {
        if (Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        } elseif (Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        } else {
            $scope = "default";
        }
        $store = is_null($o->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode() : $o->getEvent()->getStore();
        if (!Mage::helper('ebizmarts_mandrill')->useTransactionalService()) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_ACTIVE, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if (!Mage::helper('ebizmarts_mandrill')->useTransactionalService()) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE, false, $scope, $store);
            $config->saveConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if (!Mage::getStoreConfig('customer/address/dob_show')) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if (!Mage::getStoreConfig('customer/address/dob_show', $store)) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if (Mage::getStoreConfig('advanced/modules_disable_output/Mage_Wishlist', $store)) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_ACTIVE, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
        if (Mage::getStoreConfig('advanced/modules_disable_output/Mage_Review', $store)) {
            $config = Mage::getModel('core/config');
            $config->saveConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_ACTIVE, false, $scope, $store);
            Mage::getConfig()->cleanCache();
        }
    }

    public function actionAfter(Varien_Event_Observer $o)
    {
        if ($o->getEvent()->getControllerAction()->getFullActionName() == 'review_product_post') {
            Mage::dispatchEvent("review_product_post_after", array('request' => $o->getControllerAction()->getRequest()));
        }
        return $o;
    }

    public function reviewProductPostAfter(Varien_Event_Observer $o)
    {
        $params = Mage::app()->getRequest()->getParams();
        $storeId = Mage::app()->getStore()->getId();
        $customerGroupsCoupon = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_CUSTOMER_GROUP, $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_EMAIL, $storeId);
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_SUBJECT, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_MANDRILL_TAG, $storeId) . "_$storeId";
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));

        if (isset($params['token'])) {
            $token = $params['token'];
            $reviewData = Mage::getModel('ebizmarts_autoresponder/review')->loadByToken($token);
            if ($this->_generateReviewCoupon($reviewData)) {
                //generate coupon
                $customer = Mage::getModel('customer/customer')->load($reviewData->getCustomerId());
                $email = $customer->getEmail();
                $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                if (in_array($customer->getGroupId(), $customerGroupsCoupon)) {
                    if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_AUTOMATIC, $storeId) == Ebizmarts_Autoresponder_Model_Config::COUPON_AUTOMATIC) {
                        list($couponcode, $discount, $toDate) = $this->_createNewCoupon($storeId, $email);
                        $vars = array('couponcode' => $couponcode, 'discount' => $discount, 'todate' => $toDate, 'name' => $name, 'tags' => array($tags));
                    } else {
                        $couponcode = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_CODE);
                        $vars = array('couponcode' => $couponcode, 'name' => $name, 'tags' => array($tags));
                    }
                    $translate = Mage::getSingleton('core/translate');
                    $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                    $translate->setTranslateInLine(true);
                    Mage::helper('ebizmarts_abandonedcart')->saveMail('review coupon', $email, $name, $couponcode, $storeId);
                }
            }
        }
        return $o;
    }

    protected function _generateReviewCoupon($reviewData)
    {
        $store = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_HAS_COUPON, $store)) {
            return false;
        }
        $rc = false;
        // check if is a registered customer if not, return false
        if (!$reviewData->getCustomerId()) {
            return false;
        }
        // if the customer is registered the counter is in the customer account, so load the customer
        $customer = Mage::getModel('customer/customer')->load($reviewData->getCustomerId());
        $couponTotal = $customer->getEbizmartsReviewsCouponTotal();
        switch (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_COUNTER, $store)) {
            case Ebizmarts_Autoresponder_Model_Config::COUPON_GENERAL:
                // update the counter
                $counter = $customer->getEbizmartsReviewsCntrTotal();
                $counter++;
                $customer->setEbizmartsReviewsCntrTotal($counter)->save();
                // check if coupon must be generated
                $generalQuantity = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_GENERAL_QUANTITY, $store);
                switch (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_GENERAL_TYPE)) {
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_EACH:
                        if ($counter && $counter % $generalQuantity) {
                            $rc = true;
                        }
                        break;
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_ONCE:
                        if ($counter == $generalQuantity) {
                            $rc = true;
                        }
                        break;
                    case Ebizmarts_Autoresponder_Model_Config::TYPE_SPECIFIC:
                        if ($counter && $counter % $generalQuantity && $customer->getEbizmartsReviewsCouponTotal() <= Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_SPECIFIC_QUANTITY)) {
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
                if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_MAX) != 0 && $couponTotal >= Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_MAX)) {
                    $rc = false;
                } else {
                    if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_COUNTER, $store) == 0) {
                        if ($counter == $reviewData->getItems()) {
                            $rc = true;
                        } else {
                            $rc = false;
                        }
                    } elseif (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_COUNTER, $store) == $counter) {
                        if ($reviewData->getItems() >= Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_ORDER_ALMOST, $store)) {
                            $rc = true;
                        } else {
                            $rc = false;
                        }
                    }
                }
                break;
        }
        if ($rc) { // increase the count of coupons in the customer
            $customer->setEbizmartsReviewsCouponTotal($couponTotal + 1)->save();
        }
        return $rc;
    }

    protected function _createNewCoupon($store, $email)
    {
        $couponamount = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_DISCOUNT, $store);
        $couponexpiredays = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_EXPIRE, $store);
        $coupontype = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_DISCOUNT_TYPE, $store);
        $couponlength = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_LENGTH, $store);
        $couponlabel = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_COUPON_LABEL, $store);
        $websiteid = Mage::getModel('core/store')->load($store)->getWebsiteId();

        $fromDate = date("Y-m-d");
        $toDate = date('Y-m-d', strtotime($fromDate . " + $couponexpiredays day"));
        if ($coupontype == 1) {
            $action = 'cart_fixed';
            $discount = Mage::app()->getStore($store)->getCurrentCurrencyCode() . "$couponamount";
        } elseif ($coupontype == 2) {
            $action = 'by_percent';
            $discount = "$couponamount%";
        }
        $customer_group = new Mage_Customer_Model_Group();
        $allGroups = $customer_group->getCollection()->toOptionHash();
        $groups = array();
        foreach ($allGroups as $groupid => $name) {
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
        return array($uniqueId, $discount, $toDate);
    }

    public function orderSaved(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getEvent()->getOrder()->getStoreId();
        if(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_ACTIVE, $storeId)) {


            $original_data = $observer->getEvent()->getData('data_object')->getOrigData();
            $new_data = $observer->getEvent()->getData('data_object')->getData();

            $order = $observer->getEvent()->getOrder();
            $configStatuses = explode(',',Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_ORDER_STATUS, $storeId));

            foreach($configStatuses as $status) {
                if (isset($new_data['status']) && isset($original_data['status']) && $original_data['status'] !== $new_data['status'] && $new_data['status'] == $status) {
                    if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_ACTIVE, $storeId) && Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_TRIGGER, $storeId) == 1) {
                        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_MANDRILL_TAG, $storeId) . "_$storeId";
                        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_SUBJECT, $storeId);
                        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
                        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
                        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_TEMPLATE, $storeId);

                        //Send email
                        $translate = Mage::getSingleton('core/translate');
                        $email = $order->getCustomerEmail();
                        if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'neworder', $storeId)) {
                            $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                            $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=neworder&email=' . $email . '&store=' . $storeId;
                            $vars = array('tags' => array($tags), 'url' => $url);
                            $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                            $translate->setTranslateInLine(true);
                            Mage::helper('ebizmarts_abandonedcart')->saveMail('new order', $email, $name, "", $storeId);
                        }
                    }
                }
            }
        }

    }

}