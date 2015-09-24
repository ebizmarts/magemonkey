<?php
/**
 * Author: info@ebizmarts.com
 * Date: 9/16/15
 * Time: 12:47 PM
 * File: Hint.php
 * Module: magemonkey
 */
class Ebizmarts_Cron_Block_Adminhtml_System_Config_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'ebizmarts/cron/system/config/fieldset/hint.phtml';

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
        return (string)Mage::getConfig()->getNode('modules/Ebizmarts_Cron/version');
    }

    /**
     * @return string
     */
    public function getPxParams()
    {
        $v = (string)Mage::getConfig()->getNode('modules/Ebizmarts_Cron/version');
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
        return Mage::helper('ebizmarts_cron')->verify();
    }
}
