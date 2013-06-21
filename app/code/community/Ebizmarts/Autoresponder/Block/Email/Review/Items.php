<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/21/13
 * Time   : 1:43 PM
 * File   : Items.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Email_Review_Items  extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts/autoresponder_review_items.phtml');
    }
}
