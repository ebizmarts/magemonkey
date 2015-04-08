<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/18/14
 * Time   : 9:27 AM
 * File   : Date.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_Date extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setFormat(Varien_Date::DATE_INTERNAL_FORMAT);
        $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
        return parent::render($element);
    }
}