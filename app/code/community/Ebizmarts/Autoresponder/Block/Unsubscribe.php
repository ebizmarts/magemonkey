<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/26/13
 * Time   : 8:55 AM
 * File   : Unsubscribe.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Unsubscribe extends Mage_Core_Block_Template
{
    public function _construct() {
        parent::_construct();
        $this->setTemplate('ebizmarts/autoresponder/unsubscribe.phtml');
    }
}