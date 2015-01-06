<?php

require_once 'Ebizmarts/MageMonkeyApi/controllers/ApiController.php';

class Ebizmarts_MageMonkeyApi_Api_OrdersController extends Ebizmarts_MageMonkeyApi_ApiController {

	/**
	 * Return last 5 orders.
	 */
	public function indexAction() {

		$id = $this->getRequest()->getParam('id');

		if($id) {
			$order = Mage::getModel('sales/order')->load((int)$id);

			if(!$order->getId()) {
				$this->_setClientError(400, 4006);
	        	return;
			}
			else {
				$this->_setSuccess(200, $this->_orderData($order));
	        	return;
			}

		}
		else {

			$orderCollection = Mage::getResourceModel('sales/order_collection')
			->setPageSize(5)
			->setOrder('created_at', 'DESC')
			->load();

			//echo (string)$orderCollection->getSelect();

			$ret = array();

			foreach($orderCollection as $order) {
				$ret []= $this->_orderData($order);
			}

			$this->_setSuccess(200, $ret);
	        return;

    	}
	}

	protected function _orderData(Mage_Sales_Model_Order $order) {
		return array(
	                        'entity_id'            => (int)$order->getId(),
	                        "increment_id"         => (int)$order->getIncrementId(),
	                        'status'               => $order->getStatusLabel(),
	                        'created_at'           => $order->getCreatedAt(),
	                        'updated_at'           => $order->getUpdatedAt(),
	                        "store_id"             => (int)$order->getStoreId(),
	                        "store_name"           => $order->getStoreName(),
	                        "customer_id"          => (int)$order->getCustomerId(),
	                        "base_subtotal"        => (float)$order->getBaseSubtotal(),
	                        "subtotal"             => (float)$order->getSubtotal(),
	                        "base_grand_total"     => (float)$order->getBaseGrandTotal(),
	                        "base_total_paid"      => (float)$order->getBaseTotalPaid(),
	                        "grand_total"          => (float)$order->getGrandTotal(),
	                        "total_paid"           => (float)$order->getTotalPaid(),
	                        "tax_amount"           => (float)$order->getTaxAmount(),
	                        "discount_amount"      => (float)$order->getDiscountAmount(),
	                        "shipping_description" => (string)$order->getShippingDescription(),
	                        "shipping_amount"      => (float)$order->getShippingAmount(),
	                        "base_currency_code"   => Mage::helper('monkeyapi')->currency($order->getBaseCurrencyCode()),
	                        "order_currency_code"  => Mage::helper('monkeyapi')->currency($order->getOrderCurrencyCode()),
	                        "customer_email"       => (string)$order->getCustomerEmail(),
	                        "customer_firstname"   => (string)$order->getCustomerFirstname(),
	                        "customer_lastname"    => (string)$order->getCustomerLastname(),
	    );
	}

}