<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/26/13
 * Time   : 1:02 PM
 * File   : List.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Customer_Account_List extends Mage_Core_Block_Template
{
    public function getLists()
    {
        return Mage::helper('ebizmarts_autoresponder')->getLists();
    }
    public function getSaveUrl()
    {
        return $this->getUrl('ebizautoresponder/autoresponder/savelist');
    }

}