<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Adminhtml_AbandonedmailsController extends Mage_Adminhtml_Controller_Action
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
            ->_setActiveMenu('newsletter/ebizmarts_emails')
            ->_title($this->__('Newsletter'))->_title($this->__('Emails Sent'))
            ->_addBreadcrumb($this->__('Newsletter'), $this->__('Newsletter'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Mails'));

        return $this;
    }

    /**
     *
     */
    public function exportCsvAction()
    {
        $fileName = 'orders.csv';
        $grid = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_abandonedmails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'orders.xml';
        $grid = $this->getLayout()->createBlock('ebizmarts_abandonedcart/adminhtml_abandonedmails_grid');
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
}
