<?php

/**
 * Ecommerce360 API sent orders
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerceapi_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ecommerce360_sent_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);

        $this->_pagerVisibility = false;
        $this->_filterVisibility = false;

    }

    protected function _prepareCollection()
    {
        $orders = array();

        foreach(Mage::app()->getStores() as $storeId => $store){
            $api = Mage::getModel('monkey/api', array('store' => $storeId));
            $result = $api->ecommOrders(0, 500);
            $orders += $result['data'];
        }

        $collection = Mage::getModel('monkey/custom_collection', array($orders));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('store_id', array(
            'header'=> Mage::helper('monkey')->__('Store ID'),
            'index' => 'store_id',
        ));
        $this->addColumn('store_name', array(
            'header'=> Mage::helper('monkey')->__('Store Name'),
            'index' => 'store_name',
        ));
        $this->addColumn('order_id', array(
            'header'=> Mage::helper('monkey')->__('Order #'),
            'index' => 'order_id',
        ));
        $this->addColumn('email', array(
            'header'=> Mage::helper('monkey')->__('Email'),
            'index' => 'email',
        ));
        $this->addColumn('order_total', array(
            'header'=> Mage::helper('monkey')->__('Order Total'),
            'index' => 'order_total',
        ));
        $this->addColumn('tax_total', array(
            'header'=> Mage::helper('monkey')->__('Tax Total'),
            'index' => 'tax_total',
        ));
        $this->addColumn('ship_total', array(
            'header'=> Mage::helper('monkey')->__('Ship Total'),
            'index' => 'ship_total',
        ));
        $this->addColumn('order_date', array(
            'header'=> Mage::helper('monkey')->__('Order Date'),
            'index' => 'order_date',
        ));
        $this->addColumn('items', array(
            'header'=> Mage::helper('monkey')->__('Items'),
            'index' => 'items',
            'renderer' => 'monkey/adminhtml_ecommerceapi_renderer_items'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return FALSE;
    }
}
