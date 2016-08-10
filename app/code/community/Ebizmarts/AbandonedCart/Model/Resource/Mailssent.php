<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_AbandonedCart_Model_Resource_Mailssent extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_abandonedcart/mailssent', 'id');
    }

}