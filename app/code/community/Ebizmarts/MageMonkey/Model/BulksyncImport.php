<?php

/**
 * Bulksync import data access model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_BulksyncImport extends Mage_Core_Model_Abstract
{

    /**
     * Initialize
     *
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/bulksync_import');
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

    /**
     * Return array of statuses
     *
     * @return bool|array
     */
    public function statuses()
    {
        return unserialize($this->getImportTypes());
    }

}