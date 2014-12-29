<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Monkeyapilog_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('monkeyapi_log');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('created_at');
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('monkeyapi/log_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('created_at', array(
            'header' => Mage::helper('monkeyapi')->__('Time'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));
        $this->addColumn('call_method', array(
            'header' => Mage::helper('monkeyapi')->__('Method'),
            'index' => 'call_method',
            'renderer' => 'monkeyapi/adminhtml_widget_grid_column_renderer_callMethod',
        ));
        $this->addColumn('remote_addr', array(
            'header' => Mage::helper('monkeyapi')->__('IP'),
            'index' => 'remote_addr',
        ));
        $this->addColumn('call_time', array(
            'header' => Mage::helper('monkeyapi')->__('Call Time'),
            'index' => 'call_time',
            'renderer' => 'monkeyapi/adminhtml_widget_grid_column_renderer_callTime',
            'align' => 'right',
            'filter' => false,
            //'default' => '--'
        ));
        $this->addColumn('http_user_agent', array(
            'header' => Mage::helper('monkeyapi')->__('User Agent'),
            'index' => 'http_user_agent',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('monkeyapi')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('monkeyapi')->__('view full'),
                    'url'     => array('base' => 'adminhtml/monkeyapilog/view'),
                    'field'   => 'id',
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Varien_Object
     * @return string
     */
    public function getRowUrl($log) {
        return false;
    }

}