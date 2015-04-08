<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Abandonedmails extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'ebizmarts_abandonedcart';
        $this->_controller = 'adminhtml_abandonedmails';
        $this->_headerText = $this->__('Mails sent from autoresponders and abandoned carts');

        parent::__construct();
        $this->removeButton('add');

    }

}