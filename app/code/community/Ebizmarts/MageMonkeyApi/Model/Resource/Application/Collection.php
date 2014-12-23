<?php

class Ebizmarts_MageMonkeyApi_Model_Resource_Application_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    /**
     * Resource collection initialization
     *
     */
    protected function _construct() {
        $this->_init('monkeyapi/application');
    }

    public function setKeyFilter($key) {
        $this->addFieldToFilter('application_key', $key);
        return $this;
    }

    public function setApiKeyFilter($key) {
        $this->addFieldToFilter('application_request_key', $key);
        return $this;
    }

    public function setOnlyEnabledApiKeyFilter() {
        $this->addFieldToFilter('application_request_key', array('neq' => '*'));
        return $this;
    }

    public function setActiveDeviceFilter() {
        $this->addFieldToFilter('activated', 1);
        return $this;
    }

}