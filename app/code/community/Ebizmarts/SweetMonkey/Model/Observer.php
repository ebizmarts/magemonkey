<?php

/**
 * Event observer main model
 *
 * @author Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_SweetMonkey_Model_Observer
{

    public function saveConfig($observer)
    {
        if (!Mage::helper('core')->isModuleEnabled('TBT_Common')) {
            if (Mage::app()->getRequest()->getParam('store')) {
                $scope = 'store';
            } elseif (Mage::app()->getRequest()->getParam('website')) {
                $scope = 'website';
            } else {
                $scope = 'default';
            }

            $store = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode() : $observer->getEvent()->getStore();
            $config = new Mage_Core_Model_Config();
            $config->saveConfig('sweetmonkey/general/active', false, $scope, $store);
            Mage::getConfig()->cleanCache();
            $message = Mage::helper('sweetmonkey')->__('To activate Sweet Monkey you need to have <a href=https://www.sweettoothrewards.com/features/magento/>Sweet Tooth Rewards</a> enabled');
            Mage::getSingleton('adminhtml/session')->addWarning($message);
        }
        return $observer;
    }

    /**
     * Sende merge vars after customer logs in
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function customerLogin($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        Mage::helper('sweetmonkey')->pushVars($customer);
        return $this;
    }

    /**
     * Sende merge vars after Rewards/Customer saves
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function customerRewardSave($observer)
    {
        $obj = $observer->getEvent()->getObject();
        if ($obj instanceof TBT_Rewards_Model_Customer) {
            Mage::helper('sweetmonkey')->pushVars($obj);
        }

        return $this;
    }

    /**
     * Sende merge vars on new points transfer
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function pointsEvent($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer) {
            return $this;
        }

        Mage::helper('sweetmonkey')->pushVars($customer);

        return $this;
    }

    /**
     * Add points merge vars to MailChimp MergeVars struct
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function attachTbtMergeVars($observer)
    {

        $holder = $observer->getEvent()->getNewvars();
        $helper = Mage::helper('sweetmonkey');
        $customerHelper = Mage::helper('customer');
        $customer = $observer->getEvent()->getCustomer();
        if ($helper->enabled() && ($customer->getId())) {


            $merge = unserialize($helper->config('merge_vars'));

            if (count($merge)) {

                $tbtVars = array();
                foreach ($merge as $varTag) {
                    $tbtVars [$varTag['var_code']] = '-';
                }

                $tbtCustomer = Mage::getModel('rewards/customer')->load($customer->getId());
                //Point balance
                if (array_key_exists('PTS', $tbtVars)) {
                    $tbtVars['PTS'] = $tbtCustomer->getPointsSummary();
                }

                if (array_key_exists('POINTS', $tbtVars)) {
                    $tbtVars['POINTS'] = $tbtCustomer->getUsablePointsBalance(1);
                }

                //Earn and Spent points
                $existEarn = array_key_exists('PTSEARN', $tbtVars);
                $existSpent = array_key_exists('PTSSPENT', $tbtVars);

                if ($existEarn || $existSpent) {

                    $lastTransfers = $tbtCustomer->getTransfers()
                        ->selectOnlyActive()
                        ->addOrder('last_update_ts', Varien_Data_Collection::SORT_ORDER_DESC);

                    $spent = $earn = null;

                    if ($lastTransfers->getSize()) {
                        foreach ($lastTransfers as $transfer) {

                            if (is_null($earn) && $transfer->getQuantity() > 0) {
                                $earn = date_format(date_create_from_format('Y-m-d H:i:s', $transfer->getEffectiveStart()), 'Y-m-d');
                            } else if (is_null($spent) && $transfer->getQuantity() < 0) {
                                $spent = date_format(date_create_from_format('Y-m-d H:i:s', $transfer->getEffectiveStart()), 'Y-m-d');
                            }

                            if (!is_null($spent) && !is_null($earn)) {
                                break;
                            }

                        }
                    }

                    if ($existEarn && $earn) {
                        $tbtVars['PTSEARN'] = $earn;
                    }
                    if ($existSpent && $spent) {
                        $tbtVars['PTSSPENT'] = $spent;
                    }


                }

                //Expiration Points
                if (array_key_exists('PTSEXP', $tbtVars)) {
                    $val = Mage::getSingleton('rewards/expiry')
                        ->getExpiryDate($tbtCustomer);
                    if ($val) {
                        $val = date_format(date_create_from_format('d/m/Y', $val), 'Y-m-d');
                        $tbtVars['PTSEXP'] = $val;
                    }
                }
                foreach ($tbtVars as $key => $var) {
                    $aux = str_replace('points', '', strtolower($var));
                    $tbtVars[$key] = str_replace('no', 0, $aux);

                }

                $tbtVars = array_filter($tbtVars);
                //Add data to MailChimp merge vars
                $holder->setData($tbtVars);
            }

        }
        return $this;
    }

    /**
     * Gets a date in format YYYY-MM-DD HH:m:s and returns MM/DD/YYYY
     *
     * @param string Date in format YYYY-MM-DD
     * @return string MM/DD/YYYY
     */
    protected function _formatDateMerge($date)
    {
        return preg_replace("/(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)/", "$2/$3/$1", $date);
    }

}