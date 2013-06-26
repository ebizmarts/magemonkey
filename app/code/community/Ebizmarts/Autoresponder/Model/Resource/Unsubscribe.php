<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/25/13
 * Time   : 5:24 PM
 * File   : Unsubscribe.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Resource_Unsubscribe extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_autoresponder/unsubscribe','id');
    }

}