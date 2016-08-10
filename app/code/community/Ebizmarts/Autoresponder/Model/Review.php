<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_Review extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ebizmarts_autoresponder/review');
    }

    public function loadByToken($token)
    {
        $this->_getResource()->loadByToken($this, $token);
        return $this;
    }
}