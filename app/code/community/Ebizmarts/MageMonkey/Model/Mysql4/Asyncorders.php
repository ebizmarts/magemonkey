<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/12/14
 * Time   : 1:09 AM
 * File   : AsyncOrders.php
 * Module : Ebizmarts_MageMonkey
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Asyncorders extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('monkey/asyncorders', 'id');
    }
}