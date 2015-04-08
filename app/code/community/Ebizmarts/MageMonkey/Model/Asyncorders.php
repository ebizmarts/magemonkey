<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/12/14
 * Time   : 1:30 AM
 * File   : Asyncorders.php
 * Module : Ebizmarts_MageMonkey
 */
class Ebizmarts_MageMonkey_Model_Asyncorders extends Mage_Core_Model_Abstract
{
    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/asyncorders');
    }
}
