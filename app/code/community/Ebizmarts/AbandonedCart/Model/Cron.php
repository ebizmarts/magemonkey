<?php
class Ebizmarts_AbandonedCart_Model_Cron
{
    const EMAIL_TEMPLATE_XML_PATH = 'ebizmarts_abandonedcart/general/template';
    const EMAIL_TEMPLATE_XML_PATH_W_COUPON = 'ebizmarts_abandonedcart/general/coupon_template';

    /**
     *
     */
    public function abandoned()
    {
        $allStores = Mage::app()->getStores();
        foreach($allStores as $storeid => $val)
        {
            $this->_proccess($storeid);
        }
    }

    /**
     * @param $store
     */
    protected function _proccess($store)
    {

        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $days = Mage::getStoreConfig("ebizmarts_abandonedcart/general/days", $store);
        $maxtimes = Mage::getStoreConfig("ebizmarts_abandonedcart/general/max", $store);
        $sendcoupondays = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/sendon", $store);
        $sendcoupon = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/create", $store);

        if(!$days) {
            return;
        }
        $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($days, 'DAY'));
        $from = new Zend_Db_Expr($expr);

        // get a collection of abandoned carts
        $collection = Mage::getResourceModel('reports/quote_collection');
        //	$collection->prepareForAbandonedReport($store);
        $collection->addFieldToFilter('items_count', array('neq' => '0'))
                   ->addFieldToFilter('main_table.is_active', '1')
                   ->addSubtotal($store)
                   ->setOrder('updated_at');

        $collection->addFieldToFilter('main_table.converted_at', array(array('null'=>true),$this->_getSuggestedZeroDate()))
                   ->addFieldToFilter('main_table.updated_at', array('to' => $from))
                   ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter',array('lt' => $maxtimes));

        $collection->addFieldToFilter('main_table.customer_email', array('neq' => ''));
//        Mage::log((string)$collection->getSelect());

        // for each cart
        foreach($collection as $quote)
        {
            $url = Mage::getBaseUrl('web').'ebizmarts_abandonedcart/abandoned/loadquote?id='.$quote->getEntityId();

            $data = array('AbandonedURL'=>$url, 'AbandonedDate' => $quote->getUpdatedAt());
            // send email
            $mailsubject = 'Abandoned Cart';

            $senderid =  Mage::getStoreConfig("ebizmarts_abandonedcart/general/identity", $store);
            $sender = array('name'=>Mage::getStoreConfig("trans_email/ident_$senderid/name"), 'email'=> Mage::getStoreConfig("trans_email/ident_$senderid/email"));

            $email = $quote->getCustomerEmail();

            $name = $quote->getCustomerFirstname().' '.$quote->getCustomerLastname();
            $quote2 = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote->getId());
            if($sendcoupon && $quote2->getEbizmartsAbandonedcartCounter() + 1 == $sendcoupondays)
            {
                // create a new coupon
                list($couponcode,$discount,$toDate) = $this->_createNewCoupon($store,$email);
//                $templateId = Mage::getStoreConfig(self::EMAIL_TEMPLATE_XML_PATH_W_COUPON);
                $templateId = Mage::getStoreConfig(self::EMAIL_TEMPLATE_XML_PATH);
                $vars = array('quote'=>$quote,'url'=>$url, 'couponcode'=>$couponcode,'discount' => $discount, 'todate' => $toDate);
            }
            else {
                $templateId = Mage::getStoreConfig(self::EMAIL_TEMPLATE_XML_PATH);
                $vars = array('quote'=>$quote,'url'=>$url);

            }
            $translate = Mage::getSingleton('core/translate');
            Mage::log("sending mail to $name $email");
            Mage::getModel('core/email_template')->setTemplateSubject($mailsubject)->sendTransactional($templateId,$sender,$email,$name,$vars,$store);
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
        $couponamount = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/discount", $store);
        $couponexpiredays = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/expire", $store);
        $coupontype = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/discounttype", $store);
        $couponlength = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/length", $store);
        $couponlabel = Mage::getStoreConfig("ebizmarts_abandonedcart/coupon/couponlabel", $store);

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
                    ->setDiscountQty(1)
                    ->setDiscountStep('0')
                    ->setSimpleFreeShipping('0')
                    ->setApplyToShipping('0')
                    ->setIsRss(0)
                    ->setWebsiteIds($store);
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
