<?php

class Ebizmarts_AbandonedCart_Block_Email_Order_Items extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts_abandonedcart/email_order_items.phtml');
    }
}