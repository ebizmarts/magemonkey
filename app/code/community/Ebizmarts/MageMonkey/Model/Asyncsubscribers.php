<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/15/14
 * Time   : 12:46 PM
 * File   : Asyncsubscribers.php
 * Module : Ebizmarts_MageMonkey
 */
class Ebizmarts_MageMonkey_Model_Asyncsubscribers extends Mage_Core_Model_Abstract
{
    /**
     * Initialize model
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/asyncsubscribers');
    }
}
