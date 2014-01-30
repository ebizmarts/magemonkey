<?php

/**
 * Add valid email to Transactional email service
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Transactionalemail_Newemail extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_transactionalemail';
        $this->_blockGroup = 'monkey';
        $this->_mode       = 'newemail';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('monkey')->__('Add'));
    }

    public function getHeaderText()
    {
    	return Mage::helper('monkey')->__('Add valid email address');
    }

}
