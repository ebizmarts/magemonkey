<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/24/13
 * Time   : 4:37 PM
 * File   : Items.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Email_Wishlist_Items extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts/autoresponder_wishlist_items.phtml');
    }
}
