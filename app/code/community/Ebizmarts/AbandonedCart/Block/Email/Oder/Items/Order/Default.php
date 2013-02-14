<?php

class Ebizmarts_AbandonedCart_Block_Email_Oder_Items_Order_Default extends Mage_Core_Block_Template
{
    public function getOrder()
    {
        Mage::log(__METHOD__);
        return $this->getItem()->getOrder();
    }

    public function getItemOptions()
    {
        $result = array();
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf('%d', $value['qty']) . ' x ' . $this->escapeHtml($value['title']) . " " . $this->getItem()->getOrder()->formatPrice($value['price']);
        } else {
            return $this->escapeHtml($value);
        }
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }

    /**
     * Return product additional information block
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }
}