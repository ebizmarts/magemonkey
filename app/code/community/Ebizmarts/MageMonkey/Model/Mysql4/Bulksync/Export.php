<?php

/**
 * Mysql4 Bulksync Export model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_Mysql4_Bulksync_Export extends Mage_Core_Model_Mysql4_Abstract
{

	/**
	 * Initialize model
	 *
	 * @return void
	 */
    public function _construct()
    {
        $this->_init('monkey/bulksync_export', 'id');
    }

	/**
	 * Before save callback, set <created_at> and <updated_at> values
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return void
	 */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
    }
}