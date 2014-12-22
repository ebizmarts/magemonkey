<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_AbandonedCart_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @return string
     */
    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_AbandonedCart/version');
    }


    /**
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '30d'  => $this->__('Last 30 Days'),
            '60d'  => $this->__('Last 60 Days'),
            '90d'  => $this->__('Last 90 Days'),
            'lifetime' => $this->__('Lifetime'),
        );
    }
    public function log($message, $filename = 'Ebizmarts_AbandonedCart.log')
    {
        if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::LOG)) {
            Mage::log($message, null, $filename);
        }
    }
    public function saveMail($mailType,$mail,$name,$couponCode,$storeId)
    {
        if($couponCode!='') {
            $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
            $couponAmount = $rule->getDiscountAmount();
            switch($rule->getSimpleAction()) {
                case 'cart_fixed':
                    $couponType = 1;
                    break;
                case 'by_percent':
                    $couponType = 2;
                    break;
            }
        }
        else {
            $couponType = 0;
            $couponAmount = 0;
        }
        $sent = Mage::getModel('ebizmarts_abandonedcart/mailssent');
        $sent->setMailType($mailType)
             ->setStoreId($storeId)
             ->setCustomerEmail($mail)
             ->setCustomerName($name)
             ->setCouponNumber($couponCode)
             ->setCouponType($couponType)
             ->setCouponAmount($couponAmount)
             ->setSentAt(Mage::getModel('core/date')->gmtDate())
             ->save();
    }

    /**
     * Mandrill stats wrapper.
     * @param string $period
     * @param int|string $store
     * @return array|bool
     */
    public function mandrillStatistics($period, $store) {

        $api = new Mandrill_Message(Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::APIKEY,$store));
        $mandrillTag = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MANDRILL_TAG, $store)."_$store";
        $tags = $api->tags->info($mandrillTag);
        if(!$tags) {
            return false;
        }
        $general = (array)$tags;
        switch($period) {
            case '24h':
                $index = 'today';
                break;
            case '7d':
                $index = 'last_7_days';
                break;
            case '30d':
                $index = 'last_30_days';
                break;
            case '60d':
                $index = 'last_60_days';
                break;
            case '90d':
                $index = 'last_90_days';
                break;
            case 'lifetime':
                unset($general['stats']);
                return $general;

        }
        if(!isset($general['stats'])){
            return false;
        }
        $stats = (array)$general['stats'];
        $particular = (array)$stats[$index];
        return $particular;

    }

}