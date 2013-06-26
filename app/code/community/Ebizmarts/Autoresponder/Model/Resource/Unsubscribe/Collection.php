<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/26/13
 * Time   : 7:51 AM
 * File   : Collection.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Resource_Unsubscribe_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ebizmarts_autoresponder/unsubscribe');
    }
}