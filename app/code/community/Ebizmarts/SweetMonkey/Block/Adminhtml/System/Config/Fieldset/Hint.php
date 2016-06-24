<?php

class Ebizmarts_SweetMonkey_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_template = 'sweetmonkey/system/config/fieldset/hint.phtml';

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

    public function getSweetMonkeyVersion()
    {
        return (string)Mage::getConfig()->getNode('modules/Ebizmarts_SweetMonkey/version');
    }

    public function getPxParams()
    {

        $v = $this->getSweetMonkeyVersion();

        $sweetTooth = Mage::getStoreConfig('rewards/platform/apisubdomain');
        if (Mage::getStoreConfig('rewards/platform/is_connected')) {
            $platform = Mage::getSingleton('rewards/platform_instance');
            try {
                $account = $platform->account()->get();

                $sweetTooth .= "-" . $account['username'];

            } catch (Exception $ex) {
                Mage::logException($ex);
            }
        }

        $ext = "Sweet Monkey ({$sweetTooth});{$v}";

        $modulesArray = (array)Mage::getConfig()->getNode('modules')->children();
        $aux = (array_key_exists('Enterprise_Enterprise', $modulesArray)) ? 'EE' : 'CE';
        $mageVersion = Mage::getVersion();
        $mage = "Magento {$aux};{$mageVersion}";

        $hash = md5($ext . '_' . $mage . '_' . $ext);

        return "ext=$ext&mage={$mage}&ctrl={$hash}";

    }

}