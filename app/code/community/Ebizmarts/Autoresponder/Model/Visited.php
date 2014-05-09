<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Model_Visited extends Mage_Core_Model_Abstract
{
    public function _construct() {
        $this->_init('ebizmarts_autoresponder/visited');
    }
    public function loadByCustomerProduct($customerId,$productId,$storeId) {
        $this->_getResource()->loadByCustomerProduct($this,$customerId,$productId,$storeId);
        return $this;
    }
}