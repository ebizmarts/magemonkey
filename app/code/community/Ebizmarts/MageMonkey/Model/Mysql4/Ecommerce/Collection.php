<?php

/**
 * Ecommerce360 collection model
 *
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Ecommerce_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

	/**
	 * Initialize
	 *
	 * @return void
	 */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/ecommerce');
    }
}