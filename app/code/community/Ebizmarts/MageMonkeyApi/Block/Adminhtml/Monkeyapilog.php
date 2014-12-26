<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Monkeyapilog extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_monkeyapilog';
        $this->_blockGroup = 'monkeyapi';
        $this->_headerText = Mage::helper('monkeyapi')->__('API Calls');

        parent::__construct();

        $this->_removeButton('add');
    }

}