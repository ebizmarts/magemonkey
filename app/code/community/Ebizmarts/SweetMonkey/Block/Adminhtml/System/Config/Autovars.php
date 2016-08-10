<?php

/**
 * Renderer for automatic Merge Vars creation in MailChimp
 *
 * @author Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_SweetMonkey_Block_Adminhtml_System_Config_Autovars extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Set template to itself
     *
     * @return Ebizmarts_SweetMonkey_Block_Adminhtml_System_Config_Autovars
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('sweetmonkey/system/config/autovars.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => Mage::helper('paypal')->__($originalData['button_label']),
            'html_id' => $element->getHtmlId(),
            'snd_button_label' => Mage::helper('paypal')->__($originalData['snd_button_label']),
            'snd_html_id' => 'sandbox_' . $element->getHtmlId(),
        ));
        return $this->_toHtml();
    }
}