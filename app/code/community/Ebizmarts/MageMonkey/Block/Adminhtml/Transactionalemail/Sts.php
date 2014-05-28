<?php

/**
 * Transactional email STS grid container
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Block_Adminhtml_Transactionalemail_Sts extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'adminhtml_transactionalemail_sts';
        $this->_blockGroup = 'monkey';
        $this->_headerText = Mage::helper('monkey')->__('Verified Email Addresses');

        parent::__construct();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('store_switcher') . $this->getChildHtml('grid');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new', array('service' => 'sts', 'store' => $this->getRequest()->getParam('store', 0)));
    }

}
