<?php

/**
 * Model to handle cron tasks logic
 *
 * @author Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_SweetMonkey_Model_Cron
{

   /**
     * Push customers vars to MailChimp
     *
     * @return void
     */
    public function pushMergeVarsForCustomers()
    {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $storeId => $val) {
            if (Mage::getStoreConfig(Mage::helper('sweetmonkey')->config('active'), $storeId)) {

                $customers = Mage::getModel('rewards/customer')->getCollection()
                    ->addFieldToFilter('store_id', array('eq' => $storeId));

                foreach ($customers as $c) {
                    if (!Mage::helper('rewards/expiry')->isEnabled($c->getStoreId())) {
                        continue;
                    }

                    $customer = Mage::getModel('rewards/customer')->load($c->getId());
                    Mage::helper('sweetmonkey')->pushVars($customer);
                }
            }
        }
    }

}