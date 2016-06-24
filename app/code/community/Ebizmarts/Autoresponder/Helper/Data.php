<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get module configuration value
     *
     * @param string $value
     * @param string $store
     * @return mixed Configuration setting
     */
    public function config($value, $store = null)
    {
        $store = is_null($store) ? Mage::app()->getStore() : $store;

        $configscope = Mage::app()->getRequest()->getParam('store');
        if ($configscope && ($configscope !== 'undefined')) {
            $store = $configscope;
        }

        return Mage::getStoreConfig("ebizmarts_autoresponder/$value", $store);
    }

    /**
     * Logging facility
     *
     * @param mixed $data Message to save to file
     * @param string $filename log filename, default is <Ebizmarts_Autoresponder.log>
     * @return Mage_Core_Model_Log_Adapter
     */
    public function log($data, $filename = 'Ebizmarts_Autoresponder.log')
    {
        if ($this->config('general/enable_log') != 0) {
            return Mage::getModel('core/log_adapter', $filename)->log($data);
        }
    }

    public function getLists()
    {
        $types = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $lists = Mage::getConfig()->getNode('default/ebizmarts_autoresponder')->asArray();
        $lists['abandonedcart'] = array('listname' => Mage::helper('ebizmarts_abandonedcart')->__('Abandoned Carts List'));
        foreach ($lists as $key => $data) {
            if (isset($data['listname'])) {
                if (Mage::getStoreConfig("ebizmarts_autoresponder/$key/active", $storeId) || ($key == 'abandonedcart' && Mage::getStoreConfig("ebizmarts_abandonedcart/general/active", $storeId))) {
                    $types[$key]['listname'] = (string)$data['listname'];
                    $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
                    $email = $this->_getEmail();
                    $collection->addFieldToFilter('main_table.email', array('eq' => $email))
                        ->addFieldToFilter('main_table.list', array('eq' => $key))
                        ->addFieldToFilter('main_table.store_id', array('eq' => $storeId));
                    if ($collection->getSize() > 0) {
                        $types[$key]['checked'] = "";
                    } else {
                        $types[$key]['checked'] = "checked";
                    }
                }
            }
        }
        return $types;
    }

    protected function _getEmail()
    {
        return Mage::helper('customer')->getCustomer()->getEmail();
    }

    public function isBacktoStockEnabledForGuest()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $allowGuests = Mage::getStoreConfig("ebizmarts_autoresponder/backtostock/allow_guests", $storeId);

        return $allowGuests;
    }

    public function getCanShowJs()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig('ebizmarts_autoresponder/general/active', $storeId) && Mage::getStoreConfig('ebizmarts_autoresponder/visitedproducts/active', $storeId)) {
            if (Mage::getStoreConfig('web/url/use_store', $storeId)) {
                return 'ebizmarts/autoresponders/visitedproductsstorecodes.js';
            } else {
                return 'ebizmarts/autoresponders/visitedproducts.js';
            }

        }

    }

    public function isSetTime($setTime)
    {
        $now = date('H', Mage::getModel('core/date')->timestamp(time()));
        if ($now == $setTime) {
            return true;
        }
        $this->log('Time set on Autoresponder configuration is different than the current time.');
        return false;
    }

    public function isSubscribed($email, $list, $storeId)
    {
        $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
        $collection->addFieldtoFilter('main_table.email', array('eq' => $email))
            ->addFieldtoFilter('main_table.list', array('eq' => $list))
            ->addFieldtoFilter('main_table.store_id', array('eq' => $storeId));
        return $collection->getSize() == 0;
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

    protected function _formatDateMerge($date)
    {
        return preg_replace("/(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)\D+(\d+)/", "$2/$3/$1", $date);
    }

}