<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 1/17/13
 * Time: 3:15 PM
 */
class Ebizmarts_AbandonedCart_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->_headerText = $this->__('Abandoned Cart Dashboard (Ebizmarts)');
        parent::__construct();
        $this->setTemplate('ebizmarts/abandonedcart/dashboard/index.phtml');

    }
    protected  function _prepareLayout()
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
        $output   = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array('totals'))) {
            $output = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_dashboard_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }
}
