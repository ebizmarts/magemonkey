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
        foreach($allStores as $storeid => $val)
        {
            if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,$storeid)) {
                $this->_proccess($storeid);
            }
        }
    }

    public function cleanAbandonedCartExpiredCoupons(){
        $allStores = Mage::app()->getStores();
        foreach($allStores as $storeid => $val){
            if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::ACTIVE,$storeid)) {
                $this->_cleanCoupons($storeid);
            }
        }
    }

    /**
     * @param $store
     */
    protected function _proccess($store)
    {
        //Mage::app()->setCurrentStore($store);
        Mage::unregister('_singleton/core/design_package' );
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        Mage::getSingleton('core/design_package' )->setStore($store);

        $adapter = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $days = array(
            0 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_1, $store),
            1 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_2, $store),
            2 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_3, $store),
            3 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_4, $store),
            4 => Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::DAYS_5, $store)
        );
        $maxtimes = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MAXTIMES, $store)+1;
        $sendcoupondays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_DAYS, $store);
        $sendcoupon = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SEND_COUPON, $store);
        $firstdate = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_DATE, $store);
        $unit = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::UNIT, $store);
        $customergroups = explode(",",Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::CUSTOMER_GROUPS, $store));
        $mandrillTag = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MANDRILL_TAG, $store)."_$store";

        // iterates one time for each mail number
        for($run=0;$run<$maxtimes;$run++){
            if(!$days[$run]){
                return;
            }

            // subtract days from latest run to get difference from the actual abandon date of the cart
            $diff = $days[$run];
            if($run == 1 && $unit == Ebizmarts_AbandonedCart_Model_Config::IN_HOURS){
                $diff -= $days[0]/24;
            }elseif($run != 0){
                $diff -= $days[$run-1];
            }

            // set the top date of the carts to get
            $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($diff, 'DAY'));
            if($run == 0 && $unit == Ebizmarts_AbandonedCart_Model_Config::IN_HOURS) {
                $expr = sprintf('DATE_SUB(%s, %s)', $adapter->quote(now()), $this->_getIntervalUnitSql($diff, 'HOUR'));
            }
            $from = new Zend_Db_Expr($expr);

            // get collection of abandoned carts with cart_counter == $run
            $collection = Mage::getResourceModel('reports/quote_collection');
            $collection->addFieldToFilter('items_count', array('neq' => '0'))
                       ->addFieldToFilter('main_table.is_active', '1')
                       ->addFieldToFilter('main_table.store_id',array('eq'=>$store))
                       ->addSubtotal($store)
                       ->setOrder('updated_at');

            $collection->addFieldToFilter('main_table.converted_at', array(array('null'=>true),$this->_getSuggestedZeroDate()))
                       ->addFieldToFilter('main_table.updated_at', array('to' => $from,'from' => $firstdate))
                       ->addFieldToFilter('main_table.ebizmarts_abandonedcart_counter', array('eq' => $run));

            $collection->addFieldToFilter('main_table.customer_email', array('neq' => ''));
            if(count($customergroups)) {
                $collection->addFieldToFilter('main_table.customer_group_id', array('in', $customergroups));
            }

            // for each cart of the current run
            foreach($collection as $quote) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $removeFromQuote = false;
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if (!$product || $product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    {
                        Mage::log('AbandonedCart; ' . $product->getSku() .' is no longer present or enabled; remove from quote ' . $quote->getId() . ' for email',null,'Ebizmarts_AbandonedCart.log');
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
                            $stock->getManageStock() ||
                            ($stock->getUseConfigManageStock() && Mage::getStoreConfig('cataloginventory/item_options/manage_stock', $quote->getStoreId()))
                        )
                        && $stockQty < $item->getQty())
                    {
                        Mage::log('AbandonedCart; ' . $product->getSku() .' is no longer in stock; remove from quote ' . $quote->getId() . ' for email',null,'Ebizmarts_AbandonedCart.log');
                        $removeFromQuote = true;
                    }
                    if ($removeFromQuote)
                    {
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
                $token = md5(rand(0,9999999));
                $url = Mage::getModel('core/url')->setStore($store)->getUrl('',array('_nosid'=>true)).'ebizmarts_abandonedcart/abandoned/loadquote?id='.$quote->getEntityId().'&token='.$token;

                $data = array('AbandonedURL'=>$url, 'AbandonedDate' => $quote->getUpdatedAt());

                // send email
                $senderid =  Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SENDER, $store);
                $sender = array('name'=>Mage::getStoreConfig("trans_email/ident_$senderid/name",$store), 'email'=> Mage::getStoreConfig("trans_email/ident_$senderid/email",$store));

                $email = $quote->getCustomerEmail();

                if($this->_isSubscribed($email,'abandonedcart',$store)) {
                    $name = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
                    $quote2 = Mage::getModel('sales/quote')->loadByIdWithoutStore($quote->getId());
                    $unsubscribeUrl = Mage::getModel('core/url')->setStore($store)->getUrl() . 'ebizautoresponder/autoresponder/unsubscribe?list=abandonedcart&email=' . $email . '&store=' . $store;
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

                        $mailsubject = $this->_getMailSubject($run, $store);
                        $templateId = $this->_getTemplateId($run, $store);
                        if ($sendcoupon && $run + 1 == $sendcoupondays) {
                            //$templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                            // create a new coupon
                            if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AUTOMATIC) == 2) {
                                list($couponcode, $discount, $toDate) = $this->_createNewCoupon($store, $email);
                                $vars = array('quote' => $quote, 'url' => $url, 'couponcode' => $couponcode, 'discount' => $discount,
                                    'todate' => $toDate, 'name' => $name, 'tags' => array($mandrillTag), 'unsubscribeurl' => $unsubscribeUrl);
                            } else {
                                $couponcode = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_CODE);
                                $vars = array('quote' => $quote, 'url' => $url, 'couponcode' => $couponcode, 'name' => $name, 'tags' => array($mandrillTag), 'unsubscribeurl' => $unsubscribeUrl);
                            }
                        } else {
                            //$templateId = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::EMAIL_TEMPLATE_XML_PATH);
                            $vars = array('quote' => $quote, 'url' => $url, 'unsubscribeurl' => $unsubscribeUrl, 'tags' => array($mandrillTag));

                        }
                        Mage::app()->getTranslator()->init('frontend', true);
                        $translate = Mage::getSingleton('core/translate');
                        $mail = Mage::getModel('core/email_template')->setTemplateSubject($mailsubject)->sendTransactional($templateId, $sender, $email, $name, $vars, $store);
                        $translate->setTranslateInLine(true);
                        $quote2->setEbizmartsAbandonedcartCounter($quote2->getEbizmartsAbandonedcartCounter() + 1);
                        $quote2->setEbizmartsAbandonedcartToken($token);
                        $quote2->save();
                        Mage::helper('ebizmarts_abandonedcart')->saveMail('abandoned cart', $email, $name, $couponcode, $store);
                    }
                }
            }
        }
    }

    /**
     * @param $store
     * @param $email
     * @return array
     */
    protected function _createNewCoupon($store,$email)
    {
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('like'=>'Abandoned coupon ' . $email));
        if (!count($collection)) {
            $couponamount = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_AMOUNT, $store);
            $couponexpiredays = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_EXPIRE, $store);
            $coupontype = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_TYPE, $store);
            $couponlength = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LENGTH, $store);
            $couponlabel = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::COUPON_LABEL, $store);
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
        }else{
            $coupon = $collection->getFirstItem();
            if ($coupon->getSimpleAction() == 'cart_fixed') {
                $discount = Mage::app()->getStore($store)->getCurrentCurrencyCode() . $coupon->getDiscountAmount();
            } else{
                $discount = $coupon->getDiscountAmount();
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
    protected function _isSubscribed($email,$list,$storeId)
    {
        $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
        $collection->addFieldtoFilter('main_table.email',array('eq'=>$email))
            ->addFieldtoFilter('main_table.list',array('eq'=>$list))
            ->addFieldtoFilter('main_table.store_id',array('eq'=>$storeId));
        return $collection->getSize() == 0;
    }

    /**
     * @param $currentCount
     * @param $store
     * @return mixed|null
     */
    protected function _getMailSubject($currentCount, $store){

        $ret = NULL;
        switch($currentCount){
            case 0:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_SUBJECT, $store);
                break;
            case 1:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SECOND_SUBJECT, $store);
                break;
            case 2:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::THIRD_SUBJECT, $store);
                break;
            case 3:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FOURTH_SUBJECT, $store);
                break;
            case 4:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIFTH_SUBJECT, $store);
                break;
        }
        return $ret;

    }

    /**
     * @param $currentCount
     * @return mixed
     */
    protected function _getTemplateId($currentCount, $store){

        $ret = NULL;
        switch($currentCount){
            case 0:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIRST_EMAIL_TEMPLATE_XML_PATH, $store);
                break;
            case 1:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::SECOND_EMAIL_TEMPLATE_XML_PATH, $store);
                break;
            case 2:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::THIRD_EMAIL_TEMPLATE_XML_PATH, $store);
                break;
            case 3:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FOURTH_EMAIL_TEMPLATE_XML_PATH, $store);
                break;
            case 4:
                $ret = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::FIFTH_EMAIL_TEMPLATE_XML_PATH, $store);
                break;
        }
        return $ret;

    }

    protected function _cleanCoupons($store){
        $today = date('Y-m-d');
        $collection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('name', array('like'=>'Abandoned coupon%'))
            ->addFieldToFilter('to_date', array('lt'=> $today));

        foreach ($collection as $toDelete) {
            $toDelete->delete();
        }

    }
}
