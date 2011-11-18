<?php

class Ebizmarts_MageMonkey_Model_Mysql4_Apidebug extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('monkey/apidebug', 'debug_id');
    }
}