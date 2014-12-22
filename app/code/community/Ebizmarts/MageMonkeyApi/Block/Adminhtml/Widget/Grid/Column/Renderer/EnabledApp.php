<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Widget_Grid_Column_Renderer_EnabledApp extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {

        if($row->getApplicationRequestKey() == '*')
            $result = Mage::helper('monkeyapi')->__('No');
        else
            $result = Mage::helper('monkeyapi')->__('Yes');

        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row) {
        return $row->getApplicationRequestKey();
    }

}