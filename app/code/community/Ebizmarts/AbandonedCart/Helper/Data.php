<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @return string
     */
    public function getVersion()
    {
        return (string)Mage::getConfig()->getNode('modules/Ebizmarts_AbandonedCart/version');
    }


    /**
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d' => $this->__('Last 7 Days'),
            '30d' => $this->__('Last 30 Days'),
            '60d' => $this->__('Last 60 Days'),
            '90d' => $this->__('Last 90 Days'),
            'lifetime' => $this->__('Lifetime'),
        );
    }

    public function log($message, $filename = 'Ebizmarts_AbandonedCart.log')
    {
        if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::LOG)) {
            Mage::log($message, null, $filename);
        }
    }

    public function saveMail($mailType, $mail, $name, $couponCode, $storeId)
    {
        if ($couponCode != '') {
            $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
            $couponAmount = $rule->getDiscountAmount();
            switch ($rule->getSimpleAction()) {
                case 'cart_fixed':
                    $couponType = 1;
                    break;
                case 'by_percent':
                    $couponType = 2;
                    break;
            }
        } else {
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

    public function getTBTPoints($customerId, $storeId)
    {

        if (Mage::getStoreConfig('sweetmonkey/general/active', $storeId)) {
            $tbtCustomer = Mage::getModel('rewards/customer')->load($customerId);

            //Point balance
            $tbtVars['pts'] = $tbtCustomer->getPointsSummary();

            $tbtVars['points'] = $tbtCustomer->getUsablePointsBalance(1);

            //Earn and Spent points
            $lastTransfers = $tbtCustomer->getTransfers()
                ->selectOnlyActive()
                ->addOrder('last_update_ts', Varien_Data_Collection::SORT_ORDER_DESC);

            $spent = $earn = null;

            if ($lastTransfers->getSize()) {
                foreach ($lastTransfers as $transfer) {

                    if (is_null($earn) && $transfer->getQuantity() > 0) {
                        $earn = $this->_formatDateMerge($transfer->getEffectiveStart());
                    } else if (is_null($spent) && $transfer->getQuantity() < 0) {
                        $spent = $this->_formatDateMerge($transfer->getEffectiveStart());
                    }

                    if (!is_null($spent) && !is_null($earn)) {
                        break;
                    }

                }
            }

            if ($earn) {
                $tbtVars['ptsearn'] = $earn;
            }
            if ($spent) {
                $tbtVars['ptsspent'] = $spent;
            }

            //Expiration Points
            $val = Mage::getSingleton('rewards/expiry')
                ->getExpiryDate($tbtCustomer);
            if ($val) {
                $tbtVars['ptsexp'] = $val;
            }
            return $tbtVars;
        }
    }
}