<?php

class Ebizmarts_MageMonkeyApi_Model_Application extends Mage_Core_Model_Abstract {

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('monkeyapi/application');
    }
}