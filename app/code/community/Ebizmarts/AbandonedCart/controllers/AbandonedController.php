<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';

class Ebizmarts_AbandonedCart_AbandonedController extends Mage_Checkout_CartController
{
    /**
     *
     */
    public function loadquoteAction()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            //restore the quote
//            Mage::log($params['id']);
            $analytics = array();
            if(isset($params['utm_source'])) {
                $analytics['utm_source'] = $params['utm_source'];
            }
            if(isset($params['utm_medium'])) {
                $analytics['utm_medium'] = $params['utm_medium'];
            }
            if(isset($params['utm_campaign'])) {
                $analytics['utm_campaign'] = $params['utm_campaign'];
            }
            $quote = Mage::getModel('sales/quote')->load($params['id']);
            $url = Mage::getUrl(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::PAGE, $quote->getStoreId()));
            $first = true;
            foreach($analytics as $key => $value) {
                if($first) {
                    $char = '?';
                    $first = false;
                }
                else {
                    $char = '&';
                }
                $url .= "$char$key=$value";
            }
            if (isset($params['coupon'])) {
                $quote->setCouponCode($params['coupon']);
                $quote->save();
            }
            if ((!isset($params['token']) || (isset($params['token']) && $params['token'] != $quote->getEbizmartsAbandonedcartToken())) && Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AUTOLOGIN, $quote->getStoreId())) {
                Mage::getSingleton('customer/session')->addNotice("Your token cart is incorrect");
                $this->_redirect($url);
            } else {
                $url = Mage::getUrl(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::PAGE, $quote->getStoreId()));
                $first = true;
                foreach($analytics as $key => $value) {
                    if($first) {
                        $char = '?';
                        $first = false;
                    }
                    else {
                        $char = '&';
                    }
                    $url .= "$char$key=$value";
                }
                $quote->setEbizmartsAbandonedcartFlag(1);
                $quote->save();
                if (!$quote->getCustomerId()) {
                    $this->_getSession()->setQuoteId($quote->getId());
                    $this->getResponse()
                        ->setRedirect($url, 301);
                } else {
                    if (Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AUTOLOGIN, $quote->getStoreId())) {
                        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
                        if ($customer->getId()) {
                            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                        }
                        $this->getResponse()
                            ->setRedirect($url, 301);
                    } else {
                        if (Mage::helper('customer')->isLoggedIn()) {
                            $this->getResponse()
                                ->setRedirect($url, 301);
                        } else {
                            Mage::getSingleton('customer/session')->addNotice("Login to complete your order");
                            $this->_redirect('customer/account');
                        }
                    }
                }
            }
        }
//        $this->_redirect('checkout/cart');
    }
}