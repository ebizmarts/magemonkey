<?php

class Ebizmarts_MageMonkeyApi_Block_Adminhtml_Widget_Grid_Column_Renderer_CallMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
        $data = parent::_getValue($row);
        if (!empty($data)) {
            return '/' . $data;
        }
        return $this->getColumn()->getDefault();
    }

}