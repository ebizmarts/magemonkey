<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/18/13
 * Time: 5:11 PM
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Bar
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ebizmarts/abandonedcart/dashboard/totalbar.phtml');
    }

    /**
     * @return Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Totals|Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $period = $this->getRequest()->getParam('period', '24h');

        $collection = Mage::getResourceModel('ebizmarts_abandonedcart/order_collection')
                            ->addCreateAtPeriodFilter($period)
                            ->calculateTotals($isFilter);
//        $collection->getSelect()->join('sales_flat_quote' , 'main_table.increment_id = sales_flat_quote.reserved_order_id', 'ebizmarts_abandonedcart_flag');
        $collection->addFieldToFilter('main_table.ebizmarts_abandonedcart_flag',array('eq' => 1));



        if ($this->getRequest()->getParam('store')) {
            $collection->addFieldToFilter('main_table.store_id', $this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection->addFieldToFilter('main_table.store_id', array('in' => $storeIds));
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection->addFieldToFilter('main_table.store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('main_table.store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();


        $collection2 = Mage::getResourceModel('ebizmarts_abandonedcart/order_collection')
            ->addCreateAtPeriodFilter($period)
            ->calculateTotals($isFilter);
        if ($this->getRequest()->getParam('store')) {
            $collection2->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $collection2->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $collection2->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection2->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection2->load();

        $totals2 = $collection2->getFirstItem();

        // add totals for generated orders
        if($totals2->getQuantity()) {
            $convrate = (string)($totals->getQuantity()*100/$totals2->getQuantity());
            $convrate = round($convrate*100)/100;
        }
        else {
            $convrate = 0;
        }
        $this->addTotal($this->__('Generated Revenue'),$totals->getRevenue());
        $this->addTotal($this->__('Generated Tax'), $totals->getTax());
        $this->addTotal($this->__('Generated Shipping'), $totals->getShipping());
        $this->addTotal($this->__('Generated Orders'),$totals->getQuantity()*1,true);
        $this->addTotal($this->__('Generated Conv. Rate'),$convrate.'%',true);
        // get Mandrill statistics
        if(Mage::helper('core')->isModuleEnabled('Ebizmarts_Mandrill')
            && Mage::getConfig()->getNode()->modules->Ebizmarts_Mandrill->version > '1.0.4'
            && Mage::helper('mandrill')->useTransactionalService()) {
            if(!$isFilter) {
                $stores = Mage::app()->getStores();
                $__particular = array('sent' => 0, 'soft_bounces' => 0,'hard_bounces'=>0,'unique_opens'=>0,'unique_clicks'=>0);
                foreach($stores as $__store => $val) {
                    $storeid = Mage::app()->getStore($__store)->getId();
                    $aux = $this->__getMandrillStatistics($period,$storeid);
                    $__particular['sent'] += $aux['sent'];
                    $__particular['soft_bounces'] += $aux['soft_bounces'];
                    $__particular['hard_bounces'] += $aux['hard_bounces'];
                    $__particular['unique_opens'] += $aux['unique_opens'];
                    $__particular['unique_clicks'] += $aux['unique_clicks'];
                }
                $particular = $__particular;
            }
            else {
                $particular = $this->__getMandrillStatistics($period,$this->getRequest()->getParam('store'));
            }
            // add totals for emails
            if($particular) {

                $_sent = $particular['sent'];
                $_hard_bounces = $particular['hard_bounces'];
                $_unique_opens = $particular['unique_opens'];
                $_unique_clicks = $particular['unique_clicks'];


                //Emails Sent and Received
                $aux = $_sent - $_hard_bounces; // - $particular['soft_bounces'];
                if($aux > 0) {
                    $aux2 = $aux/ $_sent*100;
                }else{
                    $aux2 = 0;
                }
                $received = sprintf('%d (%2.2f%%)', $aux, $aux2);

                $this->addTotal($this->__('Emails Sent'), $_sent,true);
                $this->addTotal($this->__('Emails Received'), $received,true);

                //Emails Opened
                if($_unique_opens > 0) {
                    $emailsOpened = $_unique_opens / $_sent*100;
                }else{
                    $emailsOpened = 0;
                }

                $opens = sprintf('%d (%2.2f%%)', $_unique_opens, $emailsOpened);
                $this->addTotal($this->__('Emails Opened'),$opens,true);

                //Emails Clicked
                if($_unique_clicks > 0){
                    $emailsClicked = $_unique_clicks / $_unique_opens*100;
                }else{
                    $emailsClicked = 0;
                }

                $clicks = sprintf('%d (%2.2f%%)', $_unique_clicks, $emailsClicked);
                $this->addTotal($this->__('Emails Clicked'), $clicks,true);
            }
        }
    }

    /**
     * @param $period
     * @param $store
     * @return array|bool
     */
    private function __getMandrillStatistics($period,$store)
    {
        $mandrill = Mage::helper('mandrill')->api();
        $mandrill->setApiKey(Mage::helper('mandrill')->getApiKey($store));

        $tags = $mandrill->tagsInfo('AbandonedCart');
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
        $stats = (array)$general['stats'];
        $particular = (array)$stats[$index];
        return $particular;

    }
}