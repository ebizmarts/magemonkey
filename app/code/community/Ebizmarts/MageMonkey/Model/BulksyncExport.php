<?php


/**
 * Bulksync export data access model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_BulksyncExport extends Mage_Core_Model_Abstract
{

	/**
	 * Initialize
	 *
	 */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/bulksync_export');
    }

	/**
	 * Return array of lists
	 *
	 * @return bool|array
	 */
    public function lists()
    {
    	return unserialize($this->getLists());
    }
}