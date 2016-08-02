<?php

/**
 * Grid column renderer for YesNo based on TINYINT sql data type
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_Renderer_Yesno extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);

        return ($value == 1 ? Mage::helper('monkey')->__('Yes') : Mage::helper('monkey')->__('No'));
    }
}