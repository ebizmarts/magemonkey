<?php

class Ebizmarts_MageMonkey_Model_BulksyncImport extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/bulksync_import');
    }

    public function lists()
    {
    	return unserialize($this->getLists());
    }

    public function statuses()
    {
    	return unserialize($this->getImportTypes());
    }

}