<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 5/7/13
 * Time   : 11:08 PM
 * File   : Grid.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Abandonedmails_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('ebizmarts_abandonedcart_abandonedmails_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        return 'ebizmarts_abandonedcart/mailssent_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

//        $this->addColumn('mail_id', array(
//            'header'=> Mage::helper('ebizmarts_abandonedcart')->__('Mail #'),
//            'width' => '80px',
//            'type'  => 'text',
//            'index' => 'id',
//        ));
        $this->addColumn('store', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Store'),
            'type' => 'store',
            'index' => 'store_id'
        ));

//        if (!Mage::app()->isSingleStoreMode()) {
//            $this->addColumn('store_id', array(
//                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
//                'index'     => 'store_id',
//                'filter_index' => 'main_table.store_id',
//                'type'      => 'store',
//                'store_view'=> true,
//                'display_deleted' => true,
//            ));
//        }

        $this->addColumn('sent_at', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Sent At'),
            'index' => 'sent_at',
            'filter_index' => 'sent_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('customer_email', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Customer Email'),
            'index' => 'customer_email',
        ));

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Customer Name'),
            'index' => 'customer_name',
        ));

        $this->addColumn('mail_type', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Mail Type'),
            'index' => 'mail_type',
            'type' => 'options',
            'options' => $this->getMailTypeOptions(),
        ));
        $this->addColumn('coupon', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Coupon #'),
            'index' => 'coupon_number',
        ));
        $this->addColumn('coupon_type', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Coupon type'),
            'type' => 'options',
            'index' => 'coupon_type',
            'options' => Mage::getModel('Ebizmarts_AbandonedCart_Model_System_Config_Discounttype')->options(),
        ));
        $this->addColumn('coupon_amount', array(
            'header' => Mage::helper('ebizmarts_abandonedcart')->__('Coupon amount'),
            'index' => 'coupon_amount',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    protected function getMailTypeOptions()
    {
        return array('abandoned cart'=>'abandoned cart','happy birthday'=>'happy birthday','new order'=>'new order', 'related products'=>'related products', 'product review'=>'product review', 'no activity'=>'no activity', 'wishlist'=>'wishlist');
    }

}