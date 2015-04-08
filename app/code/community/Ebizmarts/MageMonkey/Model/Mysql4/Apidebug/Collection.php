<?php

/**
 * Apidebug collection model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Apidebug_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Set resource type
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/apidebug');
    }
}