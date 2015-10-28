<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_Cron
{
    /**
     *
     */
    public function autoresponder()
    {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $storeId => $val) {
            if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_ACTIVE, $storeId)) {
                $this->_processStore($storeId);
            }
        }
    }

    /**
     * @param $storeId
     */
    protected function _processStore($storeId)
    {
        //Mage::app()->setCurrentStore($storeId);
        Mage::unregister('_singleton/core/design_package');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::getSingleton('core/design_package')->setStore($storeId);

        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_CRON_TIME, $storeId))) {
            $this->_processNewOrders($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_CRON_TIME, $storeId))) {
            $this->_processRelated($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_CRON_TIME, $storeId))) {
            $this->_processReview($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_CRON_TIME, $storeId))) {
            $this->_processBirthday($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_CRON_TIME, $storeId))) {
            $this->_processNoActivity($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_CRON_TIME, $storeId))) {
            $this->_processWishlist($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_CRON_TIME, $storeId))) {
            $this->_processVisited($storeId);
        }
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_ACTIVE, $storeId) && Mage::helper('ebizmarts_autoresponder')->isSetTime(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_CRON_TIME, $storeId))) {
            $this->_processBackToStock($storeId);
        }
        $this->_cleanAutoresponderExpiredCoupons();
    }

    protected function _processNewOrders($storeId)
    {
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_CUSTOMER_GROUPS, $storeId));
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_DAYS, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_MANDRILL_TAG, $storeId) . "_$storeId";
        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_TEMPLATE, $storeId);

        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days + 1, 'DAY'));
        $from = new Zend_Db_Expr($expr);
        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $to = new Zend_Db_Expr($expr);
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('main_table.store_id', array('eq' => $storeId))
            ->addFieldToFilter('main_table.created_at', array('from' => $from, 'to' => $to));
        if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_TRIGGER, $storeId) == 2) {
            $collection->addFieldToFilter('main_table.status', array('eq' => strtolower(Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NEWORDER_ORDER_STATUS, $storeId))));
        }
        if (count($customerGroups)) {
            $collection->addFieldToFilter('main_table.customer_group_id', array('in' => $customerGroups));
        }
        foreach ($collection as $order) {
            $translate = Mage::getSingleton('core/translate');
            $email = $order->getCustomerEmail();
            if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'neworder', $storeId)) {
                $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=neworder&email=' . $email . '&store=' . $storeId;
                $vars = array('tags' => array($tags), 'url' => $url);

                $customer = Mage::getModel('customer/customer')
                    ->setStore(Mage::app()->getStore($storeId))
                    ->loadByEmail($email);
                if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                    $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                    foreach ($tbtPoints as $key => $field) {
                        if ($key == 'points') {
                            if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                $vars[$key] = $field;
                            }
                        } else {
                            $vars[$key] = $field;
                        }
                    }
                }
                $vars['order'] = $order;

                $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                $translate->setTranslateInLine(true);
                Mage::helper('ebizmarts_abandonedcart')->saveMail('new order', $email, $name, "", $storeId);
            }
        }
    }

    protected function _processBirthday($storeId)
    {
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_DAYS, $storeId);
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_CUSTOMER_GROUPS, $storeId));
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_TEMPLATE, $storeId);
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_SUBJECT, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_MANDRILL_TAG, $storeId) . "_$storeId";
        $sendCoupon = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_COUPON, $storeId);
        $customerGroupsCoupon = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_CUSTOMER_COUPON, $storeId));


        $collection = Mage::getModel('customer/customer')->getCollection();
        $date2 = date("Y-m-d H:i:s", strtotime(" + $days days"));
        $month = date("m", strtotime($date2));
        $day = date("d", strtotime($date2));
        $moreselect = "MONTH(at_dob.value) = $month AND DAY(at_dob.value) = $day";


        $collection->addAttributeToFilter('dob', array('neq' => 'null'))
            ->addFieldToFilter('store_id', array('eq' => $storeId));
        if (count($customerGroups)) {
            $collection->addFieldToFilter('group_id', array('in' => $customerGroups));
        }
        $collection->getSelect()->where($moreselect);
        foreach ($collection as $customer) {
            $translate = Mage::getSingleton('core/translate');
            $cust = Mage::getModel('customer/customer')->load($customer->getEntityId());
            $email = $cust->getEmail();
            $name = $cust->getFirstname() . ' ' . $cust->getLastname();
            if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'birthday', $storeId)) {
                $vars = array();
                $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=birthday&email=' . $email . '&store=' . $storeId;
                $couponcode = '';
                if ($sendCoupon && in_array($customer->getGroupId(), $customerGroupsCoupon)) {
                    if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_AUTOMATIC, $storeId) == Ebizmarts_Autoresponder_Model_Config::COUPON_AUTOMATIC) {
                        list($couponcode, $discount, $toDate) = $this->_createNewCoupon($storeId, $email, 'Birthday coupon');
                        $vars = array('couponcode' => $couponcode, 'discount' => $discount, 'todate' => $toDate, 'name' => $name, 'tags' => array($tags), 'url' => $url);
                    } else {
                        $couponcode = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_COUPON_CODE);
                        $vars = array('couponcode' => $couponcode, 'name' => $name, 'tags' => array($tags), 'url' => $url);
                    }

                }

                $customer = Mage::getModel('customer/customer')
                    ->setStore(Mage::app()->getStore($storeId))
                    ->loadByEmail($email);
                if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                    $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                    foreach ($tbtPoints as $key => $field) {
                        if ($key == 'points') {
                            if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                $vars[$key] = $field;
                            }
                        } else {
                            $vars[$key] = $field;
                        }
                    }
                }

                $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                $translate->setTranslateInLine(true);
                Mage::helper('ebizmarts_abandonedcart')->saveMail('happy birthday', $email, $name, $couponcode, $storeId);
            }
        }

    }

    protected function _processNoActivity($storeId)
    {
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_DAYS, $storeId);
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_CUSTOMER_GROUPS, $storeId));
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_TEMPLATE, $storeId);
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_SUBJECT, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_MANDRILL_TAG, $storeId) . "_$storeId";
        $sendCoupon = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_COUPON, $storeId);
        $customerGroupsCoupon = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_CUSTOMER_COUPON, $storeId));

        $collection = Mage::getModel('customer/customer')->getCollection();


        if (count($customerGroups)) {
            $collection->addFieldToFilter('group_id', array('in' => $customerGroups));
        }
        $collection->addFieldToFilter('store_id', array('eq' => $storeId));

        foreach ($collection as $customer) {
            $customerId = $customer->getEntityId();
            // get the orders for this customer for this store
            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection->addFieldToFilter('customer_id', array('eq' => $customerId))
                ->addFieldToFilter('store_id', array('eq' => $storeId));
            if ($orderCollection->getSize() > 0) { // if the customer has any order for this store
                $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
                $lastVisited = $logCustomer->getLoginAt();
                $limitup = date("Y-m-d H:i:s", strtotime(" - $days days"));
                $daysAux = $days + 1;
                $limitdown = date("Y-m-d H:i:s", strtotime(" - $daysAux days"));
                if ($limitup > $lastVisited && $limitdown < $lastVisited) {
                    $translate = Mage::getSingleton('core/translate');
                    $cust = Mage::getModel('customer/customer')->load($customerId);
                    $email = $cust->getEmail();
                    $name = $cust->getFirstname() . ' ' . $cust->getLastname();
                    if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'noactivity', $storeId)) {
                        $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=noactivity&email=' . $email . '&store=' . $storeId;
                        $vars = array('name' => $name, 'tags' => array($tags), 'lastlogin' => $lastVisited, 'url' => $url);

                        if ($sendCoupon && in_array($cust->getGroupId(), $customerGroupsCoupon)) {
                            if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::NOACTIVITY_AUTOMATIC, $storeId) == Ebizmarts_Autoresponder_Model_Config::COUPON_AUTOMATIC) {
                                list($couponcode, $discount, $toDate) = $this->_createNewCoupon($storeId, $email, 'No Activity coupon');
                                $vars = array('couponcode' => $couponcode, 'discount' => $discount, 'todate' => $toDate, 'name' => $name, 'tags' => array($tags), 'lastlogin' => $lastVisited, 'url' => $url);
                            } else {
                                $couponcode = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_COUPON_CODE);
                                $vars = array('couponcode' => $couponcode, 'name' => $name, 'tags' => array($tags), 'lastlogin' => $lastVisited, 'url' => $url);
                            }
                        }

                        $customer = Mage::getModel('customer/customer')
                            ->setStore(Mage::app()->getStore($storeId))
                            ->loadByEmail($email);
                        if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                            $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                            foreach ($tbtPoints as $key => $field) {
                                if ($key == 'points') {
                                    if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                        $vars[$key] = $field;
                                    }
                                } else {
                                    $vars[$key] = $field;
                                }
                            }
                        }

                        $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                        $translate->setTranslateInLine(true);
                        Mage::helper('ebizmarts_abandonedcart')->saveMail('no activity', $email, $name, "", $storeId);
                    }
                }
            }
        }

    }

    protected function _processRelated($storeId)
    {
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_CUSTOMER_GROUPS, $storeId));
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_DAYS, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_MANDRILL_TAG, $storeId) . "_$storeId";
        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_TEMPLATE, $storeId);
        $maxRelated = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_MAX, $storeId);
        $status = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::RELATED_STATUS, $storeId);

        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days + 1, 'DAY'));
        $from = new Zend_Db_Expr($expr);
        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $to = new Zend_Db_Expr($expr);
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('main_table.store_id', array('eq' => $storeId))
            ->addFieldToFilter('main_table.created_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('main_table.status', array('eq' => $status));

        if (count($customerGroups)) {
            $collection->addFieldToFilter('main_table.customer_group_id', array('in' => $customerGroups));
        }
        foreach ($collection as $order) {
            $counter = 0;
            $allRelated = array();
            foreach ($order->getAllItems() as $itemId => $item) {
                if ($maxRelated && $maxRelated < $counter) {
                    break;
                }
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                foreach ($product->getRelatedLinkCollection() as $related) {
                    if ($maxRelated && $maxRelated < $counter) {
                        break;
                    }
                    $relatedProduct = Mage::getModel('catalog/product')->load($related->getLinkedProductId());
                    $allRelated[$counter++] = $relatedProduct;
                }
            }
            if ($counter > 0) {
                $translate = Mage::getSingleton('core/translate');
                $email = $order->getCustomerEmail();
                if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'related', $storeId)) {
                    $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                    $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=related&email=' . $email . '&store=' . $storeId;
                    $vars = array('name' => $name, 'tags' => array($tags), 'related' => $allRelated, 'url' => $url);

                    $customer = Mage::getModel('customer/customer')
                        ->setStore(Mage::app()->getStore($storeId))
                        ->loadByEmail($email);
                    if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                        $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                        foreach ($tbtPoints as $key => $field) {
                            if ($key == 'points') {
                                if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                    $vars[$key] = $field;
                                }
                            } else {
                                $vars[$key] = $field;
                            }
                        }
                    }

                    $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                    $translate->setTranslateInLine(true);
                    Mage::helper('ebizmarts_abandonedcart')->saveMail('related products', $email, $name, "", $storeId);
                }
            }
        }

    }

    protected function _processReview($storeId)
    {
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_CUSTOMER_GROUPS, $storeId));
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_DAYS, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_MANDRILL_TAG, $storeId) . "_$storeId";
        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_TEMPLATE, $storeId);
        $status = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_STATUS, $storeId);

        $exprFrom = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days + 1, 'DAY'));
        $from = new Zend_Db_Expr($exprFrom);
        $exprTo = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $to = new Zend_Db_Expr($exprTo);

        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('main_table.store_id', array('eq' => $storeId))
            ->addFieldToFilter('main_table.created_at',array('from'=>$from,'to'=>$to))
            ->addFieldToFilter('main_table.status', array('eq' => $status));
//        Mage::log((string)$collection->getSelect());
        if (count($customerGroups)) {
            $collection->addFieldToFilter('main_table.customer_group_id', array('in' => $customerGroups));
        }
        foreach ($collection as $order) {
            $translate = Mage::getSingleton('core/translate');
            $email = $order->getCustomerEmail();
            if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'review', $storeId)) {
                if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_HAS_COUPON, $storeId)) {
                    srand((double)microtime() * 1000000);
                    $token = md5(rand(0, 9999999));
                    $review = Mage::getModel('ebizmarts_autoresponder/review');
                    $review->setCustomerId($order->getCustomerId())
                        ->setStoreId($storeId)
                        ->setItems($order->getTotalItemCount())
                        ->setCounter(0)
                        ->setToken($token)
                        ->setOrderId($order->getIncrementId())
                        ->save();
                }
                $name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
                $products = array();
                foreach ($order->getAllItems() as $item) {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if ($product->isConfigurable()) {
                        continue;
                    }
                    $products[] = $product;
                }
                $orderNum = $order->getIncrementId();
                $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=review&email=' . $email . '&store=' . $storeId;
                if (Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::REVIEW_HAS_COUPON, $storeId)) {
                    $vars = array('name' => $name, 'tags' => array($tags), 'products' => $products, 'ordernum' => $orderNum, 'url' => $url, 'token' => $token);
                } else {
                    $vars = array('name' => $name, 'tags' => array($tags), 'products' => $products, 'ordernum' => $orderNum, 'url' => $url);
                }

                $customer = Mage::getModel('customer/customer')
                    ->setStore(Mage::app()->getStore($storeId))
                    ->loadByEmail($email);
                if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                    $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                    foreach ($tbtPoints as $key => $field) {
                        if ($key == 'points') {
                            if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                $vars[$key] = $field;
                            }
                        } else {
                            $vars[$key] = $field;
                        }
                    }
                }

                $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                $translate->setTranslateInLine(true);
                Mage::helper('ebizmarts_abandonedcart')->saveMail('product review', $email, $name, "", $storeId);
            }
        }

    }

    protected function _processWishlist($storeId)
    {
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_CUSTOMER_GROUPS, $storeId));
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_DAYS, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_MANDRILL_TAG, $storeId) . "_$storeId";
        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::WISHLIST_TEMPLATE, $storeId);

        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days + 1, 'DAY'));
        $from = new Zend_Db_Expr($expr);
        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $to = new Zend_Db_Expr($expr);

        $collection = Mage::getModel('wishlist/item')->getCollection();
        $collection->addFieldToFilter('main_table.added_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('main_table.store_id', array('eq' => $storeId))
            ->setOrder('main_table.wishlist_id');
        $wishlist_ant = -1;
        $wishlistId = $collection->getFirstItem()->getWishlistId();
        $products = array();
        foreach ($collection as $item) {
            if ($wishlistId != $wishlist_ant) {
                if ($wishlist_ant != -1 && count($products) > 0) {
                    $translate = Mage::getSingleton('core/translate');
                    $email = $customer->getEmail();
                    if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'wishlist', $storeId)) {
                        $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                        $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=wishlist&email=' . $email . '&store=' . $storeId;
                        $vars = array('name' => $name, 'tags' => array($tags), 'products' => $products, 'url' => $url);

                        $customer = Mage::getModel('customer/customer')
                            ->setStore(Mage::app()->getStore($storeId))
                            ->loadByEmail($email);
                        if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                            $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                            foreach ($tbtPoints as $key => $field) {
                                if ($key == 'points') {
                                    if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                        $vars[$key] = $field;
                                    }
                                } else {
                                    $vars[$key] = $field;
                                }
                            }
                        }

                        $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                        $translate->setTranslateInLine(true);
                        Mage::helper('ebizmarts_abandonedcart')->saveMail('wishlist', $email, $name, "", $storeId);
                    }

                }
                $wishlist_ant = $wishlistId;
                $wishlistId = $item->getWishlistId();
                $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistId);
                $customer = Mage::getModel('customer/customer')->load($wishlist->getCustomerId());
                $products = array();
            }
            if (in_array($customer->getGroupId(), $customerGroups)) {
                $products[] = Mage::getModel('catalog/product')->load($item->getProductId());
            }
        }
        if (count($products)) {
            $translate = Mage::getSingleton('core/translate');
            $email = $customer->getEmail();
            if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'wishlist', $storeId)) {
                $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=wishlist&email=' . $email . '&store=' . $storeId;
                $vars = array('name' => $name, 'tags' => array($tags), 'products' => $products, 'url' => $url);

                $customer = Mage::getModel('customer/customer')
                    ->setStore(Mage::app()->getStore($storeId))
                    ->loadByEmail($email);
                if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                    $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                    foreach ($tbtPoints as $key => $field) {
                        if ($key == 'points') {
                            if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                $vars[$key] = $field;
                            }
                        } else {
                            $vars[$key] = $field;
                        }
                    }
                }

                $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                $translate->setTranslateInLine(true);
                Mage::helper('ebizmarts_abandonedcart')->saveMail('wishlist', $email, $name, "", $storeId);
            }
        }

    }

    protected function _processVisited($storeId)
    {
        $customerGroups = explode(",", Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_CUSTOMER_GROUPS, $storeId));
        $days = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_DAYS, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_MANDRILL_TAG, $storeId) . "_$storeId";
        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $max = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_MAX, $storeId);

        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days + 1, 'DAY'));
        $from = new Zend_Db_Expr($expr);
        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $to = new Zend_Db_Expr($expr);

        $collection = Mage::getModel('ebizmarts_autoresponder/visited')->getCollection();
        $collection
            ->addFieldToFilter('main_table.visited_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('main_table.store_id', array('eq' => $storeId));
        $collection->getSelect()->order('main_table.customer_id asc')->order('main_table.visited_at desc');

        $customerIdPrev = 0;
        $products = array();
        foreach ($collection as $item) {
            if ($customerIdPrev != $item->getCustomerId()) {
                if ($customerIdPrev != 0 && count($products) > 0) {
                    $email = $customer->getEmail();
                    if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'visitedproducts', $storeId)) {
                        $translate = Mage::getSingleton('core/translate');
                        $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                        $this->_sendVisitedProductEmail($email,$storeId,$products,$name,$tags);
                    }
                }
                $products = array();
                if ($item->getCustomerId()) {
                    $customer = Mage::getModel('customer/customer')->load($item->getCustomerId());
                    $customerIdPrev = $item->getCustomerId();
                }

            }
            if (count($products) > $max && $max != 0 || ($customer && !in_array($customer->getGroupId(), $customerGroups))) {
                continue;
            }
            $itemscollection = Mage::getModel('sales/quote')->getCollection();
            if ($item->getCustomerId()) {
                $itemscollection->addFieldToFilter('main_table.customer_id', array('eq' => $item->getCustomerId()));
            } else {
                $itemscollection->addFieldToFilter('main_table.customer_email', array('eq' => $item->getCustomerEmail()));
            }
            $itemscollection->addFieldToFilter('main_table.created_at', array('from' => $from))
                ->addFieldToFilter('main_table.is_active', array('eq' => 0));
            if (count($itemscollection) == 0) {
                $products[] = Mage::getModel('catalog/product')->load($item->getProductId());
            } else {
                continue;
            }

            if (!$item->getCustomerId()) {
                //add customer by email placed on Abandoned Cart Popup
                $translate = Mage::getSingleton('core/translate');
                $email = $item->getCustomerEmail();
                $name = 'customer';
                $this->_sendVisitedProductEmail($email,$storeId,$products,$name,$tags);
            }
        }
        if (count($products)) {
            if ($item->getCustomerId()) {
                $email = $customer->getEmail();
                if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'visitedproducts', $storeId)) {
                    $translate = Mage::getSingleton('core/translate');
                    $name = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $this->_sendVisitedProductEmail($email,$storeId,$products,$name,$tags);
                }
            } else {
                //add customer by email placed on Abandoned Cart Popup
                $email = $item->getCustomerEmail();
                $name = 'customer';
                $this->_sendVisitedProductEmail($email,$storeId,$products,$name,$tags);
            }
        }

    }
    protected function _sendVisitedProductEmail($email,$storeId,$products,$name,$tags)
    {
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::VISITED_TEMPLATE, $storeId);
        $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=visitedproducts&email=' . $email . '&store=' . $storeId;
        $vars = array('name' => $name, 'tags' => array($tags), 'products' => $products, 'url' => $url);

        $customer = Mage::getModel('customer/customer')
            ->setStore(Mage::app()->getStore($storeId))
            ->loadByEmail($email);
        if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
            $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
            foreach ($tbtPoints as $key => $field) {
                if ($key == 'points') {
                    if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                        $vars[$key] = $field;
                    }
                } else {
                    $vars[$key] = $field;
                }
            }
        }

        $translate = Mage::getSingleton('core/translate');
        $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        $translate->setTranslateInLine(true);
        Mage::helper('ebizmarts_abandonedcart')->saveMail('visitedproducts', $email, $name, "", $storeId);

    }
    /**
     * Process and send all notifications of Back To Stock
     * @param $storeId
     */
    public function _processBackToStock($storeId)
    {
//        $customerGroups = explode(",",Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_ACTIVE, $storeId));
        $tags = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_MANDRILL_TAG, $storeId) . "_$storeId";
        $mailSubject = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_SUBJECT, $storeId);
        $senderId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::GENERAL_SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));
        $templateId = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_TEMPLATE, $storeId);
        $mailType = Ebizmarts_Autoresponder_Model_Config::BACKTOSTOCK_MAIL_TYPE_NAME;

        $errorMessage = false;

        if (!$sender) {
            $errorMessage = Mage::helper('ebizmarts_autoresponder')->__('ERROR - Back to Stock Notification: No sender is specified. Check System/Configuration/MageMonkey/Autoresponders/General');
        }

        if (!$storeId) {
            $errorMessage = Mage::helper('ebizmarts_autoresponder')->__('ERROR - Back to Stock Notification: No Store ID is configured');
        }

        if (!$templateId) {
            $errorMessage = Mage::helper('ebizmarts_autoresponder')->__('ERROR - Back to Stock Notification: No templateId. Check System/Configuration/MageMonkey/Autoresponders/Back to Stock/Email Template');
        }

        if ($errorMessage) {
            Mage::helper('ebizmarts_autoresponder')->log($errorMessage);
            Mage::throwException($errorMessage);
            return;
        }


        // Retrieve those Products ids that came back to stock and are active
        // (is_active=1 means we didn't loop through all subscribers in ebizmarts_autoresponder_backtostock)
        $alert = Mage::getModel('ebizmarts_autoresponder/backtostockalert');
        $alert
            ->getCollection()
            ->addFieldToFilter('is_active', array('eq' => 1));

        if (count($alert) > 0) {

            // Loop through all products that came back to stock
            foreach ($alert->getCollection() as $productStockAlert) {

                // We'll validate if this products came back or not.
                if ($productStockAlert->getProductId()) {
                    $inventory = Mage::getModel('cataloginventory/stock_item');
                    $_product = Mage::getModel('catalog/product')->load($productStockAlert->getProductId());

                    // Check if Product is loaded
                    if (!$_product->getId()) {
                        Mage::helper('ebizmarts_autoresponder')->log('ERROR - Cannot load product ID ' . $productStockAlert->getProductId() . '. Existing alert and subscribers will now be disabled.');
                        $this->disableStockAlertsForProduct($productStockAlert->getProductId());
                        continue;
                    }

                    // Retrieve stock data for Product
                    $_stock = $inventory->loadByProduct($_product->getId());

                    if (!$_stock->getData()) {
                        Mage::helper('ebizmarts_autoresponder')->log('Cannot load Product ID ' . $productStockAlert->getProductId() . ' stock info. Check if product still exists.');
                        continue;
                    }

                    //@TODO check if this next two validations can be replaced with isSaleable()
                    // Validate if Product has Stock
                    if (!$_stock->getData('is_in_stock') || $_stock->getData('qty') == 0) {
                        Mage::helper('ebizmarts_autoresponder')->log('SKIPPED - Product ID ' . $_product->getId() . ' is not in stock yet.');
                        continue;
                    }

                    // Validate if Product is Enabled
                    if ($_product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                        Mage::helper('ebizmarts_autoresponder')->log('SKIPPED - Product ID ' . $_product->getId() . ' is not enabled (status = disabled).');
                        continue;
                    }

                } else {
                    Mage::helper('ebizmarts_autoresponder')->log('ERROR - Cannot retrieve Product ID value from "' . $alert->getResource()->getMainTable() . '" table.');
                    continue;
                }


                // We'll select all subscribers that has the same product_id and are active
                // (is_active=1 means we didn't contact subscribers)
                $collection = Mage::getModel('ebizmarts_autoresponder/backtostock')->getCollection();
                $collection
                    ->addFieldToFilter('is_active', array('eq' => 1))
                    ->addFieldToFilter('alert_id', array('eq' => $productStockAlert->getAlertId()));

                if (count($collection) > 0) {

                    // Loop through all subscribers that signed in to receive an email
                    // when this product become available again
                    foreach ($collection as $subscriber) {
                        $_email = $subscriber->getEmail();

                        if ($_email) {
                            $translate = Mage::getSingleton('core/translate');

                            $customer = Mage::getModel('customer/customer');
                            $customer->setStore(Mage::app()->getStore($storeId));
                            $customer->loadByEmail($_email);

                            $name = $customer->getFirstname() ? $customer->getFirstname() . ' ' . $customer->getLastname() : '';

                            $url = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=backtostock&email=' . $_email . '&store=' . $storeId;
                            $vars = array('name' => $name, 'tags' => array($tags), 'product' => $_product, 'url' => $url);

                            $customer = Mage::getModel('customer/customer')
                                ->setStore(Mage::app()->getStore($storeId))
                                ->loadByEmail($_email);
                            if ($customer->getId() && Mage::helper('sweetmonkey')->enabled()) {
                                $tbtPoints = Mage::helper('ebizmarts_autoresponder')->getTBTPoints($customer->getId(), $storeId);
                                foreach ($tbtPoints as $key => $field) {
                                    if ($key == 'points') {
                                        if ($field >= Mage::getStoreConfig('sweetmonkey/general/email_points', $storeId)) {
                                            $vars[$key] = $field;
                                        }
                                    } else {
                                        $vars[$key] = $field;
                                    }
                                }
                            }

                            $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $_email, $name, $vars, $storeId);

                            $translate->setTranslateInLine(true);
                            Mage::helper('ebizmarts_abandonedcart')->saveMail($mailType, $_email, $name, "", $storeId);

                            // Flag/Disable notification that we already send
                            $subscriber->setIsActive(0);
                            $subscriber->save();
                            Mage::helper('ebizmarts_autoresponder')->log($_email . ' notified sent for "' . $_product->getName() . '"');

                            unset($mail);
                        }

                    }

                }

                unset($collection);
                // Since there's no subscribers to contact -or all of them have been already contacted-
                // we'll deactivate this alert so another cron parses this an deletes it.
                $productStockAlert->setIsActive(0);
                $productStockAlert->save();
            }

        }

        unset($alert);
    }

    /**
     * Back to Stock : Disable Alerts and Subscribers for a specific Product ID
     * @param int $product_id
     * @return bool
     */
    private function disableStockAlertsForProduct($product_id)
    {
        if (!$product_id) {
            return false;
        }

        $stockAlert = Mage::getModel('ebizmarts_autoresponder/backtostockalert')->getCollection();
        $stockAlert->addFieldToFilter('is_active', array('eq' => 1));
        $stockAlert->addFieldToFilter('product_id', array('eq' => $product_id));

        if ($stockAlert->getSize() > 0) {
            foreach ($stockAlert as $alert) {
                $alert_id = $alert->getAlertId();

                $subscribers = Mage::getModel('ebizmarts_autoresponder/backtostock')->getCollection();
                $subscribers->addFieldToFilter('alert_id', array('eq' => $alert_id));

                foreach ($subscribers as $subscriber) {
                    $subscriber->setIsActive(0);
                    $subscriber->save();
                }

                $alert->setIsActive(0);
                $alert->save();
            }
        }

        Mage::helper('ebizmarts_autoresponder')->log('Back to Stock Notifications deactivated in database for Product ID ' . $product_id);
    }

    /**
     * Remove records from BackToStock tables which were flagged as is_active=0
     *
     */
    public function cleanupBackToStock()
    {
        // Retrieve all records that were deactivated
        $stockAlert = Mage::getModel('ebizmarts_autoresponder/backtostockalert')->getCollection();
        $stockAlert->addFieldToFilter('is_active', array('eq' => 0));

        Mage::helper('ebizmarts_autoresponder')->log('MageMonkey Autoresponder BackToStock Cleanup - started');

        if ($stockAlert->count() > 0) {
            Mage::helper('ebizmarts_autoresponder')->log('Cleaning out ' . $stockAlert->count() . ' backtostockalert.');
            foreach ($stockAlert as $alert) {
                $alert->delete();
            }
        }


        // Retrieve all records that were deactivated
        $backToStock = Mage::getModel('ebizmarts_autoresponder/backtostock')->getCollection();
        $backToStock->addFieldToFilter('is_active', array('eq' => 0));

        if ($backToStock->count() > 0) {

            Mage::helper('ebizmarts_autoresponder')->log('Cleaning out ' . $backToStock->count() . ' backtostock.');
            foreach ($backToStock as $subscriber) {
                $subscriber->delete();
            }

        }

        unset($stockAlert);
        unset($backToStock);

        Mage::helper('ebizmarts_autoresponder')->log('MageMonkey Autoresponder BackToStock Cleanup - finished');
    }


    protected function _createNewCoupon($store, $email, $string)
    {
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('like' => $string . $email));
        if (!count($collection)) {
            $couponamount = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_DISCOUNT, $store);
            $couponexpiredays = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_EXPIRE, $store);
            $coupontype = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_DISCOUNT_TYPE, $store);
            $couponlength = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_LENGTH, $store);
            $couponlabel = Mage::getStoreConfig(Ebizmarts_Autoresponder_Model_Config::BIRTHDAY_COUPON_LABEL, $store);
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
            $coupon_rule->setName($string . ' ' . $email)
                ->setDescription($string . ' ' . $email)
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
        } else {
            $coupon = $collection->getFirstItem();
            if ($coupon->getSimpleAction() == 'cart_fixed') {
                $discount = Mage::app()->getStore($store)->getCurrentCurrencyCode() . ($coupon->getDiscountAmount() + 0);
            } else {
                $discount = $coupon->getDiscountAmount() + 0;
            }
            return array($coupon->getCode(), $discount, $coupon->getToDate());
        }
    }

    function _getIntervalUnitSql($interval, $unit)
    {
        return sprintf('INTERVAL %d %s', $interval, $unit);
    }

    protected function _cleanAutoresponderExpiredCoupons()
    {
        $today = date('Y-m-d');
        $reviewCouponFilter = array('like' => 'Review coupon%');
        $birthdayCouponFilter = array('like' => 'Birthday coupon%');
        $noActivityCouponFilter = array('like' => 'No Activity coupon%');

        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array($reviewCouponFilter, $birthdayCouponFilter, $noActivityCouponFilter))
            ->addFieldToFilter('to_date', array('lt' => $today));

        foreach ($collection as $toDelete) {
            $toDelete->delete();
        }
    }
}