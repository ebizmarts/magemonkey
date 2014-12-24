<?php

class Ebizmarts_MageMonkeyApi_Model_Resource_Log_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    /**
     * Resource collection initialization
     *
     */
    protected function _construct() {
        $this->_init('monkeyapi/log');
    }

}