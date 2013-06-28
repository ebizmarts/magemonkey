<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/28/13
 * Time   : 3:13 PM
 * File   : Hint.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Adminhtml_System_Config_Wishlist_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'ebizmarts/autoresponder/system/config/wishlist/fieldset/hint.phtml';

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }


}