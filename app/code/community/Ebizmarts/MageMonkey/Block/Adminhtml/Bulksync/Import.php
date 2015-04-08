<?php

/**
 * Bulksync import form container
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Bulksync_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_bulksync';
        $this->_blockGroup = 'monkey';
        $this->_mode = 'import';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('monkey')->__('All set!'));
    }

    public function getHeaderText()
    {
        return Mage::helper('monkey')->__('New Import');
    }

}