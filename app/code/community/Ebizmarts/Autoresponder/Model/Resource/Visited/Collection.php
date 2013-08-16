<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/15/13
 * Time   : 8:06 PM
 * File   : Collection.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Resource_Visited_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ebizmarts_autoresponder/visited');
    }
}