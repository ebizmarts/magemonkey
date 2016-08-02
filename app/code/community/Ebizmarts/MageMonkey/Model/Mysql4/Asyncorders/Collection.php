<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/12/14
 * Time   : 1:17 AM
 * File   : Collection.php
 * Module : Ebizmarts_MageMonkey
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Asyncorders_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Set resource type
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/asyncorders');
    }
}