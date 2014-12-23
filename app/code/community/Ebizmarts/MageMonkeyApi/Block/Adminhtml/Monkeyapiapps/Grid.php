<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Monkeyapiapps_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();

        $this->setId('monkeyapi_apps');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('monkeyapi/application_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('monkeyapi')->__('ID'),
            'index' => 'id',
            'type' => 'number',
        ));
        $this->addColumn('application_key', array(
            'header' => Mage::helper('monkeyapi')->__('Device Key'),
            'index' => 'application_key',
            //'type' => 'number',
        ));
        $this->addColumn('activated', array(
            'header' => Mage::helper('monkeyapi')->__('Activated?'),
            'index' => 'activated',
            'type' => 'options',
            'filter' => false,
            'options' => array(1 => Mage::helper('monkeyapi')->__('Yes'), 0 => Mage::helper('monkeyapi')->__('No')),
        ));
        $this->addColumn('enabled', array(
            'header' => Mage::helper('monkeyapi')->__('Enabled?'),
            'index' => 'enabled',
            'filter' => false,
            'renderer' => 'monkeyapi/adminhtml_widget_grid_column_renderer_enabledApp',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('monkeyapi')->__('Action'),
            'width' => '80px',
            'type' => 'action',
            'align' => 'center',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('monkeyapi')->__('Enable/Disable'),
                    'url'     => array('base' => 'adminhtml/monkeyapiapps/toggle'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('monkeyapi')->__('Are you sure?')
                ),
                array(
                    'caption' => Mage::helper('monkeyapi')->__('Reset'),
                    'url'     => array('base' => 'adminhtml/monkeyapiapps/reset'),
                    'field'   => 'id',
                    'confirm' => Mage::helper('monkeyapi')->__('Are you sure?')
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ));

        //$this->addExportType('*/*/exportCsv', Mage::helper('bakerloo_restful')->__('CSV'));

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
    public function getRowUrl($item) {
        return false; //$this->getUrl('*/*/edit', array('id' => $item->getId()));
    }

}