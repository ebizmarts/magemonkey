<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/18/13
 * Time: 4:34 PM
 */

class Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Bar
{
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ebizmarts/abandonedcart/dashboard/salebar.phtml');


    }

    /**
     * @return Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard_Sales|Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');

        $collection = Mage::getResourceModel('ebizmarts_abandonedcart/order_collection')
            ->calculateSales($isFilter);
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
        }
        $collection->load();
        $sales = $collection->getFirstItem();

        $this->addTotal($this->__('Lifetime Generate Revenue'), $sales->getLifetime());
        $this->addTotal($this->__('Average Orders'), $sales->getAverage());
    }

}