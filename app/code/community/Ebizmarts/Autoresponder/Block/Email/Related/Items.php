<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/19/13
 * Time   : 2:50 PM
 * File   : Items.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Email_Related_Items  extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts/autoresponder_related_items.phtml');
    }
}
