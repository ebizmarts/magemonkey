<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Monkeyapiapps extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Block constructor
     */
    public function __construct() {
        $this->_controller = 'adminhtml_monkeyapiapps';
        $this->_blockGroup = 'monkeyapi';
        $this->_headerText = Mage::helper('monkeyapi')->__('Apps');

        parent::__construct();

       // $this->_removeButton('add');
    }

}