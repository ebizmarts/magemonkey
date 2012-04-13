<?php

/**
 * Ecommerce360 controller, perform mass actions and show grid
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Adminhtml_EcommerceController extends Mage_Adminhtml_Controller_Action
{

	/**
	 * Display already sent orders
	 */
	public function indexAction()
	{
        $this->_title($this->__('Newsletter'))
             ->_title($this->__('MailChimp'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter/magemonkey');
        $this->renderLayout();
	}

	/**
	 * Just the grid contents for AJAX requests
	 */
	public function gridAction()
	{
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('monkey/adminhtml_ecommerce_grid')->toHtml()
        );
	}

	/**
	 * Display already sent ALL orders from API
	 */
	public function apiordersAction()
	{
        $this->_title($this->__('Newsletter'))
             ->_title($this->__('MailChimp'));

        $this->loadLayout();
        $this->_setActiveMenu('newsletter/magemonkey');
        $this->renderLayout();
	}

	/**
	 * Mass action send order to mailchimp
	 */
	public function masssendAction()
	{
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $sent     = 0;
        $notSent  = 0;

        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);

            $result = Mage::getModel('monkey/ecommerce360')->logSale($order);

            if ($result === TRUE) {
                $sent++;
            } else {
            	$this->_getSession()->addError($this->__('Error on order #%s, - %s -', $order->getIncrementId(), $result));
                $notSent++;
            }
        }
        if ($notSent) {
            if ($sent) {
                $this->_getSession()->addError($this->__('%s order(s) were not sent.', $notSent));
            } else {
                $this->_getSession()->addError($this->__('No order(s) were sent successfully.'));
            }
        }
        if ($sent) {
            $this->_getSession()->addSuccess($this->__('%s order(s) have been sent successfully.', $sent));
        }
        $this->_redirect('adminhtml/sales_order/index');
	}

}
