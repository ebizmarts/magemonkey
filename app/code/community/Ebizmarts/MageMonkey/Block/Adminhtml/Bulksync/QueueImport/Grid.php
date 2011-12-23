<?php

class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_QueueImport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('bulksync_importjobs_queue');
        $this->setUseAjax(TRUE);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(TRUE);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('monkey/bulksyncImport')
					  	->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		//TODO: Add, reset and delete actions
        $this->addColumn('id', array(
            'header'=> Mage::helper('monkey')->__('ID'),
            'index' => 'id',
            'type' => 'number'
        ));

        $this->addColumn('status', array(
            'header'=> Mage::helper('monkey')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getModel('monkey/system_config_source_bulksyncStatus')->toOption()
        ));

        $this->addColumn('processed_count', array(
            'header'=> Mage::helper('monkey')->__('# Processed'),
            'index' => 'processed_count',
            'type' => 'number'
        ));

        $this->addColumn('import_types', array(
            'header'=> Mage::helper('monkey')->__('Import'),
            'index' => 'import_types',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Importypes'
        ));

        $this->addColumn('lists', array(
            'header'=> Mage::helper('monkey')->__('Lists'),
            'index' => 'lists',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Lists'
        ));

        $this->addColumn('create_customer', array(
            'header'=> Mage::helper('monkey')->__('Create Customer'),
            'index' => 'create_customer',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Yesno'
        ));

        $this->addColumn('since', array(
            'header'=> Mage::helper('monkey')->__('Retrieve Since'),
            'index' => 'since',
            'renderer' => 'Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Date'
        ));

        $this->addColumn('updated_at', array(
            'header'=> Mage::helper('monkey')->__('Updated At'),
            'index' => 'updated_at',
            'type'  => 'datetime'
        ));

        $this->addColumn('created_at', array(
            'header'=> Mage::helper('monkey')->__('Created At'),
            'index' => 'created_at',
            'type'  => 'datetime'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return FALSE;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/importgrid', array('_current' => true));
    }
}