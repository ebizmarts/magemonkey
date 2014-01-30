<?php

/**
 * Ecommerce360 db access model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_Ecommerce extends Mage_Core_Model_Abstract
{
	/**
	 * Initialize model
	 *
	 * @return void
	 */
    public function _construct()
    {
        parent::_construct();
        $this->_init('monkey/ecommerce');
    }
}
