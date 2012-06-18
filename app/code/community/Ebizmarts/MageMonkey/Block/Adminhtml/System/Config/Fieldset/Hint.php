<?php

/**
 * Renderer for SagePay banner in System Configuration
 * @author      Ebizmart Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'magemonkey/system/config/fieldset/hint.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getMageMonkeyVersion()
    {
    	return (string) Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
    }

	public function getPxParams() {

	$v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_MageMonkey/version');
	$ext = "MageMonkey;{$v}";

	$modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
	$aux = (array_key_exists('Enterprise_Enterprise', $modulesArray))? 'EE' : 'CE' ;
	$mageVersion = Mage::getVersion();
	$mage = "Magento {$aux};{$mageVersion}";

	$hash = md5($ext . '_' . $mage . '_' . $ext);

    return "ext=$ext&mage={$mage}&ctrl={$hash}";

    }
}

