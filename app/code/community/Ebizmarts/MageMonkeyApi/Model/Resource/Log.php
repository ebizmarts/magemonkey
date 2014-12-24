<?php

class Ebizmarts_MageMonkeyApi_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract {

    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('monkeyapi/log', 'id');
    }

    /**
     * Action before save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Api_Model_Resource_Role
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId()) {
            $object->setCreatedAt($this->formatDate(true));
        }
        $object->setUpdatedAt($this->formatDate(true));
        return $this;
    }

}