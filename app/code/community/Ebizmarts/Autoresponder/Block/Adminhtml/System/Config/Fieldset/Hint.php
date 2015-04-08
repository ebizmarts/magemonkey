<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'ebizmarts/autoresponder/system/config/fieldset/hint.phtml';

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return (string)Mage::getConfig()->getNode('modules/Ebizmarts_Autoresponder/version');
    }

    /**
     * @return string
     */
    public function getPxParams()
    {
        $v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_Autoresponder/version');
        $ext = "Abandoned Cart;{$v}";

        $modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
        $aux = (array_key_exists('Enterprise_Enterprise', $modulesArray)) ? 'EE' : 'CE';
        $mageVersion = Mage::getVersion();
        $mage = "Magento {$aux};{$mageVersion}";

        $hash = md5($ext . '_' . $mage . '_' . $ext);

        return "ext=$ext&mage={$mage}&ctrl={$hash}";

    }

    /**
     * @return mixed
     */
    public function verify()
    {
        return Mage::helper('ebizmarts_autoresponder')->verify();
    }

}