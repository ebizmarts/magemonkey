<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Block_Email_Related_Items  extends Mage_Sales_Block_Items_Abstract
{
    public function _construct()
    {
        $this->setTemplate('ebizmarts/autoresponder_related_items.phtml');
    }
}
