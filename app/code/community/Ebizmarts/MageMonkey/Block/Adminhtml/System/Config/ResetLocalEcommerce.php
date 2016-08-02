<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/18/14
 * Time   : 3:23 PM
 * File   : ResetEcommerce.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_ResetLocalEcommerce extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magemonkey/system/config/resetlocal360.phtml');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        $store = $this->getRequest()->getParam('store');
        return Mage::helper('adminhtml')->getUrl('adminhtml/ecommerce/resetLocalEcommerce/store/' . $store);
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id' => 'resetecommerce_button',
                'label' => $this->helper('monkey')->__('Reset Local Orders Ecommerce360'),
                'onclick' => 'javascript:check(); return false;'
            ));

        return $button->toHtml();
    }
}