<?php

/**
 * Ecommerce360 ALL orders from api sent
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Ecommerceapi extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	$this->_blockGroup = 'monkey';
        $this->_controller = 'adminhtml_ecommerceapi';
        $this->_headerText = Mage::helper('monkey')->__('Ecommerce360 API Orders');

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
