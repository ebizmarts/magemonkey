<?php

class Ebizmarts_MageMonkey_Model_Ecommerce extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/ecommerce');
    }
}