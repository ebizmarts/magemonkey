<?php

/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 10/28/15
 * Time   : 3:23 PM
 * File   : UpgradeForPatch.php
 * Module : magemonkey
 */
class Ebizmarts_MageMonkey_Block_Adminhtml_System_Config_UpgradeForPatch extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magemonkey/system/config/upgradeforpatch.phtml');
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
    public function getAjaxUpgrade()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/config/upgradeForPatch');
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
                'id' => 'upgradeforpatch_button',
                'label' => $this->helper('monkey')->__('Upgrade for Patch SUPEE-6788'),
                'onclick' => 'javascript:upgradeForPatch(); return false;'
            ));

        return $button->toHtml();
    }

    protected function tableExists(){
        $prefix = Mage::getConfig()->getTablePrefix();
        if($prefix[0]){
            $pre = $prefix[0];
        }else{
            $pre = '';
        }
        $resource = Mage::getSingleton('core/resource')
            ->getConnection('core_write');

        $table = $resource->getTableName($pre.'permission_block');
        $tableExists = (bool)$resource->showTableStatus($table);
        return $tableExists;
    }
}