<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Model_Cron
{
//    const EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template';
//    const EMAIL_TEMPLATE_XML_PATH_W_COUPON = 'ebizmarts_abandonedcart/general/coupon_template';

    /**
     *
     */
    public function abandoned()
    {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $storeid => $val) {
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE, $storeid)) {
                $this->_proccess($storeid);
            }
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ENABLE_POPUP, $storeid) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_CREATE_COUPON, $storeid)) {
                $this->_sendPopupCoupon($storeid);
            }
        }
    }

    public function cleanAbandonedCartExpiredCoupons()
    {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $storeid => $val) {
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE, $storeid)) {
                $this->_cleanCoupons($storeid);
            }
        }
    }

    /**
     * @param $storeId
     */
    protected function _proccess($storeId)
    {
        //Mage::app()->setCurrentStore($storeId);
        Mage::unregister('_singleton/core/design_package');
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::getSingleton('core/design_package')->setStore($storeId);

        $abTesting = false;
        $item = Mage::getModel('ebizmarts_abandonedcart/abtesting')->getCollection()
            ->addFieldToFilter('store_id', array('eq' => $storeId))
            ->getFirstItem();
        if ($item) {
            $status = $item->getCurrentStatus();
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, $storeId) && $status == 1) {
                $abTesting = true;
                $suffix = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_MANDRILL_SUFFIX, $storeId);
            }
        }

        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $days = array(
            0 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_1, $storeId),
            1 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_2, $storeId),
            2 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_3, $storeId),
            3 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_4, $storeId),
            4 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_5, $storeId)
        );
        $maxtimes = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $storeId) + 1;
        $sendcoupon = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $storeId);
        $firstdate = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_DATE, $storeId);
        $unit = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::UNIT, $storeId);
        $customergroups = explode(",", Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::CUSTOMER_GROUPS, $storeId));
        $mandrillTag = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MANDRILL_TAG, $storeId) . "_$storeId";

        if ($abTesting) {
            $mandrillTag .= '_' . $suffix;
            $sendcoupondays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_COUPON_SENDON, $storeId);
        } else {
            $sendcoupondays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_DAYS, $storeId);
        }

        //coupon vars
        $couponamount = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AMOUNT, $storeId);
        $couponexpiredays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_EXPIRE, $storeId);
        $coupontype = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_TYPE, $storeId);
        $couponlength = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LENGTH, $storeId);
        $couponlabel = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LABEL, $storeId);

        // iterates one time for each mail number
        for ($run = 0; $run < $maxtimes; $run++) {
            if (!$days[$run]) {
                return;
            }

            // subtract days from latest run to get difference from the actual abandon date of the cart
            $diff = $days[$run];
            if ($run == 1 && $unit == Ebizmarts_AbandonedCart_Model_Config::IN_HOURS) {
                $diff -= $days[0] / 24;
            } elseif ($run != 0) {
                $diff -= $days[$run - 1];
            }

            // set the top date of the carts to get
            $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($diff, 'DAY'));
            if ($run == 0 && $unit == Ebizmarts_AbandonedCart_Model_Config::IN_HOURS) {
                $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($diff, 'HOUR'));
            }
            $from = new Zend_Db_Expr($expr);

            // get collection of abandoned carts with cart_counter == $run
            $collection = Mage::getResourceModel('reports/quote_collection');
            $collection->addFieldToFilter('items_count', array('neq' => '0'))
                ->addFieldToFilter('main_table.is_active', '1')
                ->addFieldToFilter('main_table.store_id', array('eq' => $storeId))
                ->addSubtotal($storeId)
                ->setOrder('updated_at');

            $collection->addFieldToFilter('main_table.converted_at', array(array('null' => true), $this->_getSuggestedZeroDate()))
                ->addFieldToFilter('main_table.updated_at', array('to' => $from, 'from' => $firstdate))
                ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter', array('eq' => $run));

            $collection->addFieldToFilter('main_table.customer_email', array('neq' => ''));
            if (count($customergroups)) {
                $collection->addFieldToFilter('main_table.customer_group_id', array('in' => $customergroups));
            }

            // for each cart of the current run
            foreach ($collection as $quote) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $removeFromQuote = false;
                    $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($item->getProductId());
                    if (!$product || $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        Mage::log('AbandonedCart; ' . $product->getSku() . ' is no longer present or enabled; remove from quote ' . $quote->getId() . ' for email', null, 'Ebizmarts_AbandonedCart.log');
                        $removeFromQuote = true;
                    }

                    if ($product->getTypeId() == 'configurable') {
                        $simpleProductId = Mage::getModel('catalog/product')->getIdBySku($item->getSku());
                        $simpleProduct = Mage::getModel('catalog/product')->load($simpleProductId);
                        $stock = $simpleProduct->getStockItem();
                        $stockQty = $stock->getQty();
                    } elseif ($product->getTypeId() == 'bundle') {
                        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        $bundled_product = new Mage_Catalog_Model_Product();
                        $bundled_product->load($product->getId());
                        $selectionCollection = $bundled_product->getTypeInstance(true)->getSelectionsCollection(
                            $bundled_product->getTypeInstance(true)->getOptionsIds($bundled_product), $bundled_product
                        );
                        $stockQty = -1;
                        foreach ($selectionCollection as $option) {
                            foreach ($options['bundle_options'] as $bundle) {
                                if ($bundle['value'][0]['title'] == $option->getName()) {
                                    $label = $bundle['label'];
                                    $qty = $bundle['value'][0]['qty'];
                                    if ($stockQty == -1 || $stockQty > $qty) {
                                        $stockQty = $qty;
                                    }
                                }
                            }
                        }

                    } else {
                        $stock = $product->getStockItem();
                        $stockQty = $stock->getQty();
                    }

                    if (
                        (
                            is_object($stock) && ($stock->getManageStock() ||
                                ($stock->getUseConfigManageStock() && Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $quote->getStoreId())))
                        )
                        && $stockQty < $item->getQty()
                    ) {
                        Mage::log('AbandonedCart; ' . $product->getSku() . ' is no longer in stock; remove from quote ' . $quote->getId() . ' for email', null, 'Ebizmarts_AbandonedCart.log');
                        $removeFromQuote = true;
                    }
                    if ($removeFromQuote) {
                        $quote->removeItem($item->getId());
                    }
                }

                if (count($quote->getAllVisibleItems()) < 1) {
                    $quote2 = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote->getId());
                    $quote2->setEbizmartsAbandonedcartCounter($quote2->getEbizmartsAbandonedcartCounter() + 1);
                    $quote2->save();
                    continue;
                }
                // check if they are any order from the customer with date >=
                $collection2 = Mage::getResourceModel('reports/quote_collection');
                $collection2->addFieldToFilter('main_table.is_active', '0')
                    ->addFieldToFilter('main_table.reserved_order_id', array('neq' => 'NULL'))
                    ->addFieldToFilter('main_table.customer_email', array('eq' => $quote->getCustomerEmail()))
                    ->addFieldToFilter('main_table.updated_at', array('from' => $quote->getUpdatedAt()));
                if ($collection2->getSize()) {
                    continue;
                }
                //
                //$url = Mage::getBaseUrl('web').'ebizmarts_abandonedcart/abandoned/loadquote?id='.$quote->getEntityId();
                //srand((double)microtime()*1000000);
                $token = md5(rand(0, 9999999));
                if ($abTesting) {
                    $url = Mage::getModel('core/url')->setStore($storeId)->getUrl('', array('_nosid' => true)) . 'ebizmarts_abandonedcart/abandoned/loadquote?id=' . $quote->getEntityId() . '&token=' . $token . '&' . $suffix;
                } else {
                    $url = Mage::getModel('core/url')->setStore($storeId)->getUrl('', array('_nosid' => true)) . 'ebizmarts_abandonedcart/abandoned/loadquote?id=' . $quote->getEntityId() . '&token=' . $token;
                }

                $data = array('AbandonedURL' => $url, 'AbandonedDate' => $quote->getUpdatedAt());

                // send email
                $senderid = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SENDER, $storeId);
                $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderid/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderid/email", $storeId));

                $email = $quote->getCustomerEmail();

                if (Mage::helper('ebizmarts_autoresponder')->isSubscribed($email, 'abandonedcart', $storeId)) {
                    $name = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
                    $quote2 = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote->getId());
                    $unsubscribeUrl = Mage::getModel('core/url')->setStore($storeId)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=abandonedcart&email=' . $email . '&store=' . $storeId;
                    $couponcode = '';

                    //if hour is set for first run calculates hours since cart was created else calculates days
                    $today = idate('U', strtotime(now()));
                    $updatedAt = idate('U', strtotime($quote2->getUpdatedAt()));
                    $updatedAtDiff = ($today - $updatedAt) / 60 / 60 / 24;
                    if ($unit == Ebizmarts_AbandonedCart_Model_Config::IN_HOURS && $run == 0) {
                        $updatedAtDiff = ($today - $updatedAt) / 60 / 60;
                    }

                    // if days have passed proceed to send mail
                    if ($updatedAtDiff >= $diff) {
                        $mailsubject = $this->_getMailSubject($run, $abTesting, $storeId);
                        $templateId = $this->_getTemplateId($run, $abTesting, $storeId);
                        if ($sendcoupon && $run + 1 == $sendcoupondays) {
                            //$templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                            // create a new coupon
                            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AUTOMATIC) == 2) {
                                list($couponcode, $discount, $toDate) = $this->_createNewCoupon($storeId, $email, $couponamount, $couponexpiredays, $coupontype, $couponlength, $couponlabel);
                                $url .= '&coupon=' . $couponcode;
                                $vars = array('quote' => $quote, 'url' => $url, 'couponcode' => $couponcode, 'discount' => $discount,
                                    'todate' => $toDate, 'name' => $name, 'tags' => array($mandrillTag), 'unsubscribeurl' => $unsubscribeUrl);
                            } else {
                                $couponcode = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_CODE);
                                $url .= '&coupon=' . $couponcode;
                                $vars = array('quote' => $quote, 'url' => $url, 'couponcode' => $couponcode, 'name' => $name, 'tags' => array($mandrillTag), 'unsubscribeurl' => $unsubscribeUrl);
                            }
                        } else {
                            //$templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                            $vars = array('quote' => $quote, 'url' => $url, 'unsubscribeurl' => $unsubscribeUrl, 'tags' => array($mandrillTag));

                        }

                        $customer = Mage::getModel('customer/customer')
                            ->setStore(Mage::app()->getStore($storeId))
                            ->loadByEmail($email);
                        if ($customer->getId()) {
                            $tbtPoints = Mage::helper('ebizmarts_abandonedcart')->getTBTPoints($customer->getId(), $storeId);
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

                        Mage::app()->getTranslator()->init('frontend', true);
                        $translate = Mage::getSingleton('core/translate');
                        $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailsubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
                        $translate->setTranslateInLine(true);
                        $quote2->setEbizmartsAbandonedcartCounter($quote2->getEbizmartsAbandonedcartCounter() + 1);
                        $quote2->setEbizmartsAbandonedcartToken($token);
                        $quote2->save();

                        if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_ACTIVE, $storeId)) {
                            $counterCollection = Mage::getModel('ebizmarts_abandonedcart/abtesting')->getCollection()
                                ->addFieldToFilter('store_id', array('eq' => $storeId));
                            $counter = $counterCollection->getFirstItem();
                            $counter->setCurrentStatus($counter->getCurrentStatus() + 1)
                                ->save();
                        }
                        Mage::helper('ebizmarts_abandonedcart')->saveMail('abandoned cart', $email, $name, $couponcode, $storeId);
                    }
                }
            }
        }
    }

    protected function _sendPopupCoupon($storeId)
    {
        $templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_TEMPLATE_XML_PATH, $storeId);
        $mailSubject = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_MAIL_SUBJECT, $storeId);
        $tags = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_MANDRILL_TAG, $storeId) . "_$storeId";
        $senderId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SENDER, $storeId);
        $sender = array('name' => Mage::getStoreConfig("trans_email/ident_$senderId/name", $storeId), 'email' => Mage::getStoreConfig("trans_email/ident_$senderId/email", $storeId));


        //coupon vars
        $couponamount = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_DISCOUNT, $storeId);
        $couponexpiredays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_EXPIRE, $storeId);
        $coupontype = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_DISCOUNTTYPE, $storeId);
        $couponlength = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_LENGTH, $storeId);
        $couponlabel = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_LABEL, $storeId);

        $collection = Mage::getModel('ebizmarts_abandonedcart/popup')->getCollection()
            ->addFieldToFilter('email', array('neq' => ''))
            ->addFieldToFilter('processed', array('eq' => 0));

        foreach ($collection as $item) {
            $email = $item->getEmail();
            $emailArr = explode('@', $email);
            $pseudoName = $emailArr[0];
            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_AUTOMATIC, $storeId) == 2) {
                list($couponcode, $discount, $toDate) = $this->_createNewCoupon($storeId, $email, $couponamount, $couponexpiredays, $coupontype, $couponlength, $couponlabel);
                $vars = array('couponcode' => $couponcode, 'discount' => $discount, 'todate' => $toDate, 'name' => $pseudoName, 'tags' => array($tags));
            } else {
                $couponcode = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::POPUP_COUPON_CODE);
                $vars = array('couponcode' => $couponcode, 'name' => $pseudoName, 'tags' => array($tags));
            }
            $translate = Mage::getSingleton('core/translate');
            $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailSubject)->sendTransactional($templateId, $sender, $email, $pseudoName, $vars, $storeId);
            $item->setProcessed(1)->save();
            $translate->setTranslateInLine(true);
            Mage::helper('ebizmarts_abandonedcart')->saveMail('review coupon', $email, $pseudoName, $couponcode, $storeId);
        }
    }

    /**
     * @param $store
     * @param $email
     * @return array
     */
    protected function _createNewCoupon($store, $email, $couponamount, $couponexpiredays, $coupontype, $couponlength, $couponlabel)
    {
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('like' => 'Abandoned coupon ' . $email));
        if (!count($collection)) {

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
            $coupon_rule->setName("Abandoned coupon $email")
                ->setDescription("Abandoned coupon $email")
                ->setStopRulesProcessing(0)
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

    /**
     * @param $interval
     * @param $unit
     * @return string
     */
    function _getIntervalUnitSql($interval, $unit)
    {
        return sprintf('INTERVAL %d %s', $interval, $unit);
    }

    /**
     * @return string
     */
    function _getSuggestedZeroDate()
    {
        return '0000-00-00 00:00:00';
    }

    protected function _isSubscribed($email, $list, $storeId)
    {
        $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
        $collection->addFieldtoFilter('main_table.email', array('eq' => $email))
            ->addFieldtoFilter('main_table.list', array('eq' => $list))
            ->addFieldtoFilter('main_table.store_id', array('eq' => $storeId));
        return $collection->getSize() == 0;
    }

    /**
     * @param $currentCount
     * @param $store
     * @return mixed|null
     */
    protected function _getMailSubject($currentCount, $abTesting = false, $store)
    {

        $ret = NULL;
        switch ($currentCount) {
            case 0:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FIRST_SUBJECT, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_SUBJECT, $store);
                }
                break;
            case 1:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_SECOND_SUBJECT, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SECOND_SUBJECT, $store);
                }
                break;
            case 2:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_THIRD_SUBJECT, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::THIRD_SUBJECT, $store);
                }
                break;
            case 3:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FOURTH_SUBJECT, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FOURTH_SUBJECT, $store);
                }
                break;
            case 4:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FIFTH_SUBJECT, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIFTH_SUBJECT, $store);
                }
                break;
        }
        return $ret;

    }

    /**
     * @param $currentCount
     * @return mixed
     */
    protected function _getTemplateId($currentCount, $abTesting = false, $store)
    {

        $ret = NULL;
        switch ($currentCount) {
            case 0:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FIRST_EMAIL, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_EMAIL_TEMPLATE_XML_PATH, $store);
                }
                break;
            case 1:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_SECOND_EMAIL, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SECOND_EMAIL_TEMPLATE_XML_PATH, $store);
                }
                break;
            case 2:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_THIRD_EMAIL, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::THIRD_EMAIL_TEMPLATE_XML_PATH, $store);
                }
                break;
            case 3:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FOURTH_EMAIL, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FOURTH_EMAIL_TEMPLATE_XML_PATH, $store);
                }
                break;
            case 4:
                if ($abTesting) {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AB_TESTING_FIFTH_EMAIL, $store);
                } else {
                    $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIFTH_EMAIL_TEMPLATE_XML_PATH, $store);
                }
                break;
        }
        return $ret;

    }

    protected function _cleanCoupons($store)
    {
        $today = date('Y-m-d');
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('like' => 'Abandoned coupon%'))
            ->addFieldToFilter('to_date', array('lt' => $today));

        foreach ($collection as $toDelete) {
            $toDelete->delete();
        }

    }
}
