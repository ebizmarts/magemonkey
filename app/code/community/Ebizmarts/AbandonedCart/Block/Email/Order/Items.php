<?php

class Ebizmarts_AbandonedCart_Block_Email_Order_Items extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts_abandonedcart/email_order_items.phtml');
    }

    public function getTax($_item)
    {
		if (Mage::helper('tax')->displayCartPriceInclTax()){
			$subtotal = Mage::helper('tax')->__('Incl. Tax') . ' : ' .Mage::helper('checkout')->formatPrice($_item['row_total_incl_tax']);
		} elseif(Mage::helper('tax')->displayCartBothPrices()) {
			$subtotal = Mage::helper('tax')->__('Excl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total']) . '<br>'. Mage::helper('tax')->__('Incl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total_incl_tax']);
		} else {
			$subtotal = Mage::helper('tax')->__('Excl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total']);
		}
		return $subtotal;
    }

}