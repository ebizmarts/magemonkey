<?php

class Ebizmarts_MageMonkey_Model_BulksyncExport extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/bulksync_export');
    }

    public function lists()
    {
    	return unserialize($this->getLists());
    }
}