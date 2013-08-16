<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/15/13
 * Time   : 8:04 PM
 * File   : Visited.php
 * Module : Ebizmarts_Magemonkey
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