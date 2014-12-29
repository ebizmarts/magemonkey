<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Widget_Grid_Column_Renderer_CallTime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $data = parent::_getValue($row);
        if (!is_null($data)) {

            return Mage::helper('monkeyapi')->formatTimeSeconds($data) .'s';
        }
        return $this->getColumn()->getDefault();
    }

}