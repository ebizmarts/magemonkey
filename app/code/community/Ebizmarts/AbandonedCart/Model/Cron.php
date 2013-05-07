<?php
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
        foreach($allStores as $storeid => $val)
        {
            if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,$storeid)) {
                $this->_proccess($storeid);
            }
        }
    }

    /**
     * @param $store
     */
    protected function _proccess($store)
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $days = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS, $store);
        $maxtimes = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $store);
        $sendcoupondays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_DAYS, $store);
        $sendcoupon = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $store);
        $firstdate = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_DATE, $store);
        $unit = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::UNIT, $store);
        $customergroups = explode(",",Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::CUSTOMER_GROUPS, $store));

        if(!$days) {
            return;
        }
        if($unit == Ebizmarts_AbandonedCart_Model_Config::IN_DAYS) {
            $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
            $from = new Zend_Db_Expr($expr);

            // get a collection of abandoned carts
            $collection = Mage::getResourceModel('reports/quote_collection');
            $collection->addFieldToFilter('items_count', array('neq' => '0'))
                       ->addFieldToFilter('main_table.is_active', '1')
                       ->addFieldToFilter('main_table.store_id',array('eq'=>$store))
                       ->addSubtotal($store)
                       ->setOrder('updated_at');

            $collection->addFieldToFilter('main_table.converted_at', array(array('null'=>true),$this->_getSuggestedZeroDate()))
                       ->addFieldToFilter('main_table.updated_at', array('to' => $from,'from' => $firstdate))
                       ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter',array('lt' => $maxtimes));

            $collection->addFieldToFilter('main_table.customer_email', array('neq' => ''));
            if(count($customergroups)) {
                $collection->addFieldToFilter('main_table.customer_group_id', array('in', $customergroups));
            }
        } else {
            // make the collection for first run
            $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'HOUR'));
            $from = new Zend_Db_Expr($expr);

            // get a collection of abandoned carts
            $collection1 = Mage::getResourceModel('reports/quote_collection');
            $collection1->addFieldToFilter('items_count', array('neq' => '0'))
                ->addFieldToFilter('main_table.is_active', '1')
                ->addFieldToFilter('main_table.store_id',array('eq'=>$store))
                ->addSubtotal($store)
                ->setOrder('updated_at');


            $collection1->addFieldToFilter('main_table.converted_at', array(array('null'=>true),$this->_getSuggestedZeroDate()))
                ->addFieldToFilter('main_table.updated_at', array('to' => $from,'from' => $firstdate))
                ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter',array('eq' => 0));

            $collection1->addFieldToFilter('main_table.customer_email', array('neq' => ''));
            if(count($customergroups)) {
                $collection1->addFieldToFilter('main_table.customer_group_id', array('in', $customergroups));
            }

            $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql(1, 'DAY'));
            $from = new Zend_Db_Expr($expr);
            // get a collection of abandoned carts who aren't the first run
            $collection2 = Mage::getResourceModel('reports/quote_collection');
            $collection2->addFieldToFilter('items_count', array('neq' => '0'))
                ->addFieldToFilter('main_table.is_active', '1')
                ->addFieldToFilter('main_table.store_id',array('eq'=>$store))
                ->addSubtotal($store)
                ->setOrder('updated_at');

            $collection2->addFieldToFilter('main_table.converted_at', array(array('null'=>true),$this->_getSuggestedZeroDate()))
                ->addFieldToFilter('main_table.updated_at', array('to' => $from,'from' => $firstdate))
                ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter',array('from' => 1,'to' => $maxtimes-1));

            $collection2->addFieldToFilter('main_table.customer_email', array('neq' => ''));
            if(count($customergroups)) {
                $collection2->addFieldToFilter('main_table.customer_group_id', array('in', $customergroups));
            }

            Mage::log((string)$collection1->getSelect());
            Mage::log((string)$collection2->getSelect());
            $collection = $collection1;
            foreach($collection2 as $quote) {
                $collection->addItem($quote);
            }
        }

        // for each cart
        foreach($collection as $quote)
        {
            // ckeck if they are any order from the customer with date >=
            $collection2 = Mage::getResourceModel('reports/quote_collection');
            $collection2->addFieldToFilter('main_table.is_active', '0')
                        ->addFieldToFilter('main_table.reserved_order_id',array('neq' => 'NULL' ))
                        ->addFieldToFilter('main_table.customer_email',array('eq' => $quote->getCustomerEmail()))
                        ->addFieldToFilter('main_table.updated_at',array('from'=>$quote->getUpdatedAt()));
            if($collection2->getSize()) {
                continue;
            }
            //
            //$url = Mage::getBaseUrl('web').'ebizmarts_abandonedcart/abandoned/loadquote?id='.$quote->getEntityId();
            $url = Mage::getModel('core/url')->setStore($store)->getUrl().'ebizmarts_abandonedcart/abandoned/loadquote?id='.$quote->getEntityId();

            $data = array('AbandonedURL'=>$url, 'AbandonedDate' => $quote->getUpdatedAt());
            // send email
            $mailsubject = 'Abandoned Cart';

            $senderid =  Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SENDER, $store);
            $sender = array('name'=>Mage::getStoreConfig("trans_email/ident_$senderid/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_$senderid/email"));

            $email = $quote->getCustomerEmail();

            $name = $quote->getCustomerFirstname().' '.$quote->getCustomerLastname();
            $quote2 = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote->getId());
            if($sendcoupon && $quote2->getEbizmartsAbandonedcartCounter() + 1 == $sendcoupondays)
            {
                $templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                // create a new coupon
                if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AUTOMATIC)==2) {
                    list($couponcode,$discount,$toDate) = $this->_createNewCoupon($store,$email);
                    $vars = array('quote'=>$quote,'url'=>$url, 'couponcode'=>$couponcode,'discount' => $discount, 'todate' => $toDate, 'name' => $name);
                }
                else {
                    $couponcode = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_CODE);
                    $vars = array('quote'=>$quote,'url'=>$url, 'couponcode'=>$couponcode, 'name' => $name);
                }
            }
            else {
                $templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                $vars = array('quote'=>$quote,'url'=>$url);

            }
            $translate = Mage::getSingleton('core/translate');
            $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailsubject)->sendTransactional($templateId,$sender,$email,$name,$vars,$store);
            $translate->setTranslateInLine(true);
            $quote2->setEbizmartsAbandonedcartCounter($quote2->getEbizmartsAbandonedcartCounter()+1);
            $quote2->save();
        }

    }

    /**
     * @param $store
     * @param $email
     * @return array
     */
    protected function _createNewCoupon($store,$email)
    {
        $couponamount = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AMOUNT, $store);
        $couponexpiredays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_EXPIRE, $store);
        $coupontype = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_TYPE, $store);
        $couponlength = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LENGTH, $store);
        $couponlabel = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LABEL, $store);
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

        $coupon_rule = Mage::getModel('salesrule/rule');
        $coupon_rule->setName("Abandoned coupon $email")
                    ->setDescription("Abandoned coupon $email")
                    ->setFromDate($fromDate)
                    ->setToDate($toDate)
                    ->setIsActive(1)
                    ->setCouponType(2)
                    ->setUsesPerCoupon(1)
                    ->setUsesPerCustomer(1)
                    ->setCustomerGroupIds(array(0,1))
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
        $uniqueId = $coupon_rule->getCouponCodeGenerator()->generateCode();
        $coupon_rule->setCouponCode($uniqueId);
        $coupon_rule->save();
        return array($uniqueId,$discount,$toDate);
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

}
