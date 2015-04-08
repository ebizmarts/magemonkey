<?php

/**
 * Transactional email Mandrill grid container
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Mandrill_Block_Adminhtml_Users_Senders extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_users_senders';
        $this->_blockGroup = 'ebizmarts_mandrill';
        $this->_headerText = Mage::helper('ebizmarts_mandrill')->__('Verified Email Addresses (%s)', "the senders that have tried to use this account, both verified and unverified.");

        parent::__construct();

        $this->removeButton('add');

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