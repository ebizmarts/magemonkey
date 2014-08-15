<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Block_Unsubscribe extends Mage_Core_Block_Template
{
    public function _construct() {
        parent::_construct();
        $this->setTemplate('ebizmarts/autoresponder/unsubscribe.phtml');
    }
}