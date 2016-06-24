<?php

/**
 * Renderer for merge vars in configuration
 *
 * @author Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_SweetMonkey_Block_Adminhtml_System_Config_Form_Field_Mapfields extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * Set the columns name
     *
     * @return void
     */
    public function __construct()
    {
        $this->addColumn('var_code', array(
            'label' => Mage::helper('sweetmonkey')->__('Code'),
            'style' => 'width:120px',
        ));
        $this->addColumn('var_label', array(
            'label' => Mage::helper('sweetmonkey')->__('Label'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('sweetmonkey')->__('Add field');
        parent::__construct();
    }
}