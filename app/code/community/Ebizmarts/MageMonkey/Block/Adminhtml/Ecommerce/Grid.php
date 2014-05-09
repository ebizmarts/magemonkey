<?php

/**
 * Ecommerce360 sent orders Grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerce_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('ecommerce360_sent_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('monkey/ecommerce')
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

        $this->addColumn('order_increment_id', array(
            'header'=> Mage::helper('monkey')->__('Order #'),
            'width' => '80px',
            'index' => 'order_increment_id',
        ));

        $this->addColumn('mc_campaign_id', array(
            'header'=> Mage::helper('monkey')->__('Campaign #'),
            'width' => '80px',
            'index' => 'mc_campaign_id'
        ));

        $this->addColumn('mc_email_id', array(
            'header'=> Mage::helper('monkey')->__('Email #'),
            'width' => '80px',
            'index' => 'mc_email_id'
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

    protected function _prepareMassaction()
    {
	    $this->setMassactionIdField('order_id');
		$this->getMassactionBlock()->setFormFieldName('orders');
		$this->getMassactionBlock()->addItem('delete', array(
		'label'=> Mage::helper('monkey')->__('Delete'),
		'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
		'confirm' => Mage::helper('tax')->__('Are you sure?')
		));
		return $this;
    }

}
