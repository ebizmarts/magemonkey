<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Adminhtml_AbandonedorderController extends Mage_Adminhtml_Controller_Action
{
    /**
     *
     */
    public function indexAction()
    {
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * @return Ebizmarts_AbandonedCart_Adminhtml_AbandonedorderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('sales/ebizmarts_abandonedcart')
            ->_title($this->__('Sales'))->_title($this->__('Abandoned'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Abandoned'));

        return $this;
    }

    /**
     *
     */
    public function exportCsvAction()
    {
        $fileName = 'orders.csv';
        $grid = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_abandonedorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'orders.xml';
        $grid = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_abandonedorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     *
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     *
     */
    public function dashboardAction()
    {
//        $this->_initAction()
//            ->renderLayout();
        $this->loadLayout()
            ->_setActiveMenu('dashboard/ebizmarts_abandonedcart')
            ->_title($this->__('Dashboard'))->_title($this->__('Abandoned'))
            ->_addBreadcrumb($this->__('Dashboard'), $this->__('Dashboard'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Abandoned'))
            ->renderLayout();

    }

    /**
     *
     */
    public function ajaxBlockAction()
    {
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');
        if ($blockTab == 'totals') {
            $output = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_dashboard_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }

    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'dashboard':
            case 'ajaxBlock':
                $acl = 'dashboard/ebizmarts_abandonedcart';
                break;
            case 'index':
            case 'grid':
            case 'exportExcel':
            case 'exportCsv':
                $acl = 'sales/ebizmarts_abandonedcart';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
