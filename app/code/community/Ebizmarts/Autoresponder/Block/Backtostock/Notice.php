<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Autoresponder_Block_Backtostock_Notice extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ebizmarts/autoresponder/backtostock/catalog/product/notice.phtml');
    }

    public function isLoggedIn()
    {
        return Mage::helper('customer')->isLoggedIn();
    }


    public function getCustomerEmail()
    {
        $email = false;

        if(!$this->isLoggedIn()) {
            return $email;
        }

        if(Mage::helper('customer')->getCurrentCustomer()) {
            $email = Mage::helper('customer')->getCurrentCustomer()->getEmail();
        }

        return $email;
    }

    public function getProduct()
    {
        $_product = Mage::registry('current_product');

        return $_product;
    }

    public function getSubscribeUrl()
    {
        $actionUrl = $this->getUrl('ebizmarts_autoresponder/backtostock/subscribe');
        return $actionUrl;
    }

}