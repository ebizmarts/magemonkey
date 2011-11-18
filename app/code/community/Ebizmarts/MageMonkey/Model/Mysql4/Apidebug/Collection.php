<?php

class Ebizmarts_MageMonkey_Model_Mysql4_Apidebug_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/apidebug');
    }
}