<?php

/**
 * Ecommerce360 Mysql4 model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Model_Mysql4_Ecommerce extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Initialize
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('monkey/ecommerce', 'id');
    }
}