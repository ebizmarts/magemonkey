<?php

class Ebizmarts_MageMonkey_Model_Mysql4_Bulksync_Import extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('monkey/bulksync_import', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
    }
}