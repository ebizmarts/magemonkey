<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/22/13
 * Time   : 5:20 PM
 * File   : Review.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_Review extends Mage_Core_Model_Abstract
{
    public function _construct() {
        $this->_init('ebizmarts_autoresponder/review');
    }
    public function loadByToken($token) {
        $this->_getResource()->loadByToken($this,$token);
        return $this;
    }
}