<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 7/15/13
 * Time   : 1:26 PM
 * File   : Collection.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Model_Resource_Mailssent_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ebizmarts_abandonedcart/mailssent');
    }
}

