<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/16/13
 * Time: 2:16 PM
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Abandonedorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'ebizmarts_abandonedcart';
        $this->_controller = 'adminhtml_abandonedorder';
        $this->_headerText = $this->__('Orders made from abandoned carts (Ebizmarts)');

        parent::__construct();
        $this->removeButton('add');

    }

}