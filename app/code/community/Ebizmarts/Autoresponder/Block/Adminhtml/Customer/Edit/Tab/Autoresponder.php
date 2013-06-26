<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/26/13
 * Time   : 10:38 AM
 * File   : Autoresponder.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Block_Adminhtml_Customer_Edit_Tab_Autoresponder   extends Mage_Adminhtml_Block_Widget_Form
                                                                                implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ebizmarts/autoresponder/customer/tab/newsletter.phtml');
    }
    public function getTabLabel() {
        return $this->__('Autoresponder subscriptions');
    }
    public function getTabTitle() {
        return $this->__('Autoresponder subscriptions');
    }
    public function canShowTab()
    {
        return true;
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }
    public function isHidden()
    {
        return false;
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }
}