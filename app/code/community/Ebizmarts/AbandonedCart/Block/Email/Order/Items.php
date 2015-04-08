<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Block_Email_Order_Items extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts_abandonedcart/email_order_items.phtml');
    }

    public function getTax($_item)
    {
        if (Mage::helper('tax')->displayCartPriceInclTax()) {
            $subtotal = Mage::helper('tax')->__('Incl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total_incl_tax']);
        } elseif (Mage::helper('tax')->displayCartBothPrices()) {
            $subtotal = Mage::helper('tax')->__('Excl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total']) . '<br>' . Mage::helper('tax')->__('Incl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total_incl_tax']);
        } else {
            $subtotal = Mage::helper('tax')->__('Excl. Tax') . ' : ' . Mage::helper('checkout')->formatPrice($_item['row_total']);
        }
        return $subtotal;
    }

    public function getImage($_item)
    {
        $product = Mage::getModel('catalog/product')
            ->load($_item->getProductId());
        if ($product->getImage() == "no_selection" && $product->getTypeId() == "configurable") {
            $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
            $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
            foreach ($simple_collection as $simple_product) {
                if ($simple_product->getImage() != "no_selection") {
                    $imageUrl = $simple_product->getThumbnailUrl();
                }
            }
        } else {
            $imageUrl = $product->getThumbnailUrl();
        }
        return $imageUrl;
    }

}