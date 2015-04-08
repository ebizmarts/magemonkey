<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Abandonedorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('ebizmarts_abandonedcart_abandonedorder_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    /**
     * @return this
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        $sales_flat_order_table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
        $collection->getSelect()->join($sales_flat_order_table, 'main_table.increment_id = ' . $sales_flat_order_table . '.increment_id', 'ebizmarts_abandonedcart_flag');
        $collection->addFieldToFilter($sales_flat_order_table . '.ebizmarts_abandonedcart_flag', array('eq' => 1));
        return parent::_prepareCollection();
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sales')->__('Purchased From (Store)'),
                'index' => 'store_id',
                'filter_index' => 'main_table.store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'main_table.status',
            'type' => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));


        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * @return Ebizmarts_AbandonedCart_Block_Adminhtml_Abandonedorder_Grid|Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        return $this;

    }

    /**
     * @param $row
     * @return bool|string
     */
    public function getRowUrl($row)
    {
//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
//            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
//        }
        return false;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}