<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('bulksync_jobs_queue');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('monkey/bulksyncExport')
					  	->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('id', array(
            'header'=> Mage::helper('monkey')->__('ID'),
            'width' => '80px',
            'index' => 'id',
            'type' => 'number'
        ));

        $this->addColumn('status', array(
            'header'=> Mage::helper('monkey')->__('Status'),
            'width' => '80px',
            'index' => 'status',
        ));

        $this->addColumn('updated_at', array(
            'header'=> Mage::helper('monkey')->__('Date Sent'),
            'width' => '80px',
            'index' => 'updated_at',
            'type'  => 'datetime'
        ));

        $this->addColumn('created_at', array(
            'header'=> Mage::helper('monkey')->__('Date Sent'),
            'width' => '80px',
            'index' => 'created_at',
            'type'  => 'datetime'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getOrderId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}