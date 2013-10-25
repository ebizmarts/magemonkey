<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/22/13
 * Time   : 5:22 PM
 * File   : Collection.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Resource_Review_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ebizmarts_autoresponder/review');
    }
}