<?php

class Ebizmarts_MageMonkey_Model_Apidebug extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/apidebug');
    }
}