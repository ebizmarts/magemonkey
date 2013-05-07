<?php

/**
 * Custom renderer for Oauth2 authorization wizard popup
 * 
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_OauthWizard extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * Set template to itself
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('magemonkey/system/config/oauth_wizard.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $originalData = $element->getOriginalData();
        
        $label = $originalData['button_label'];
        
        //Check if api key works
        $ping = Mage::getModel('monkey/api');
        $ping->ping();
        if(!$ping->errorCode){
			$label = "Change API credentials";
		}
        
        $this->addData(array(
            'button_label' => $this->helper('monkey')->__($label),
            'button_url'   => $this->helper('monkey/oauth2')->authorizeRequestUrl(),
            'html_id' => $element->getHtmlId(),
        ));
        return $this->_toHtml();
    }
}
