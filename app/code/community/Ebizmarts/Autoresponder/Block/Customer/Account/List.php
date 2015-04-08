<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Block_Customer_Account_List extends Mage_Core_Block_Template
{
    public function getLists()
    {
        return Mage::helper('ebizmarts_autoresponder')->getLists();
    }

    public function getSaveUrl()
    {
        return $this->getUrl('ebizautoresponder/autoresponder/savelist');
    }

}