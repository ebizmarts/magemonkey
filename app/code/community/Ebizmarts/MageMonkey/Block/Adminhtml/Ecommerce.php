<?php

/**
 * Ecommerce360 sent orders Grid container
 *
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerce extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	$this->_blockGroup = 'monkey';
        $this->_controller = 'adminhtml_ecommerce';
        $this->_headerText = Mage::helper('monkey')->__('Ecommerce360 Sent Orders');

        parent::__construct();

        $this->_removeButton('add');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }

}