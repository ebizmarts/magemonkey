<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 7/15/13
 * Time   : 1:24 PM
 * File   : MailsSent.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Model_Resource_Mailssent extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_abandonedcart/mailssent','id');
    }

}