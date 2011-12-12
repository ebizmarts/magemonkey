<?php

class Ebizmarts_MageMonkey_Model_Mysql4_Bulksync_Export_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/bulksync_export');
    }

    function setItemObjectClass($className)
    {
        $this->_itemObjectClass = 'Ebizmarts_MageMonkey_Model_BulksyncExport';
        return $this;
    }

}