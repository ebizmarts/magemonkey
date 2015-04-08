<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/16/14
 * Time   : 5:18 PM
 * File   : Templates.php
 * Module : magemonkey
 */
class Ebizmarts_Mandrill_Block_Adminhtml_Templates_Templates extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_templates_templates';
        $this->_blockGroup = 'ebizmarts_mandrill';
        $this->_headerText = Mage::helper('ebizmarts_mandrill')->__('Mandrill Templates');

        parent::__construct();

//    $this->removeButton('add');

    }

    public function getGridHtml()
    {
        return $this->getChildHtml('store_switcher') . $this->getChildHtml('grid');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('store' => $this->getRequest()->getParam('store', 0)));
    }

}