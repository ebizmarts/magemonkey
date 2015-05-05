<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->_headerText = $this->__('Abandoned Cart Dashboard (Ebizmarts)');
        parent::__construct();
        $this->setTemplate('ebizmarts/abandonedcart/dashboard/index.phtml');

    }

    protected function _prepareLayout()
    {
        $this->setChild('sales',
            $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_dashboard_sales')
        );
        $this->setChild('totals',
            $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_dashboard_totals')
        );


    }

    public function ajaxBlockAction()
    {
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array('totals'))) {
            $output = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_dashboard_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
    }
}
