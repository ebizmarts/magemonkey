<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/19/14
 * Time   : 12:52 AM
 * File   : ResetRemoteEcommerce.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_ResetRemoteEcommerce extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magemonkey/system/config/resetremote360.phtml');
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
        return Mage::helper('adminhtml')->getUrl('adminhtml/ecommerce/resetRemoteEcommerce/store/' . $store);
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
                'label' => $this->helper('monkey')->__('Reset Remote Orders Ecommerce360'),
                'onclick' => 'javascript:check2(); return false;'
            ));

        return $button->toHtml();
    }
}