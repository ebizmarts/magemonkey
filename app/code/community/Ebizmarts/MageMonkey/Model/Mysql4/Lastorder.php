<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 9/12/14
 * Time   : 1:14 AM
 * File   : Asyncsubscribers.php
 * Module : Ebizmarts_MageMonkey
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Lastorder extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('monkey/lastorder', 'id');
    }
}