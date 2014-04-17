<?php

require_once Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php';

class Ebizmarts_AbandonedCart_AbandonedController extends Mage_Checkout_CartController
{
    /**
     *
     */
    public function loadquoteAction()
    {
        $params = $this->getRequest()->getParams();
        if(isset($params['id']))
        {
            //restore the quote
//            Mage::log($params['id']);

            $quote = Mage::getModel('sales/quote')->load($params['id']);
            if(!isset($params['token']) || (isset($params['token'])&&$params['token']!=$quote->getEbizmartsAbandonedcartToken())) {
                Mage::getSingleton('customer/session')->addNotice("Your token cart is incorrect");
                $this->_redirect('/');
            }
            else {
                $url = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::PAGE,$quote->getStoreId());
                $quote->setEbizmartsAbandonedcartFlag(1);
                $quote->save();
                if(!$quote->getCustomerId()) {
                    $this->_getSession()->setQuoteId($quote->getId());
                    $this->_redirect($url);
                }
                else {
                    if(Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::AUTOLOGIN,$quote->getStoreId())) {
                        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
                        if($customer->getId())
                        {
                            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                        }
                        $this->_redirect($url);
                    }
                    else {
                        if(Mage::helper('customer')->isLoggedIn()) {
                            $this->_redirect($url);
                        }
                        else {
                            Mage::getSingleton('customer/session')->addNotice("Login to complete your order");
                            $this->_redirect('customer/account');
                        }
                    }
                }
            }
        }
//        $this->_redirect('checkout/cart');
    }

    public function captureEmailAction()
    {
        $oSession = Mage::getSingleton('checkout/session');
        $oQuote = $oSession->getQuote();

        $vCustomerEmail = $this->getRequest()->getParam('customer_email');

        if(Zend_Validate::is($vCustomerEmail, 'EmailAddress')){
            $couponCode = Mage::getStoreConfig(Ebizmarts_AbandonedCart_Model_Config::MODAL_COUPON_CODE);
            if($couponCode){
                try{
                    $oQuote->setCustomerEmail($vCustomerEmail)
                        ->setCouponCode($couponCode)
                        ->save();

                    $jResponse = json_encode(Array('success' => true, 'message' => 'Your discount has been applied to your cart.'));
                }catch(Exception $e){
                    Mage::logException($e);
                    $jResponse = json_encode(Array('success' => false, 'message' => 'Session failed to save.'));
                }
            }

        }else{
            $jResponse = json_encode(Array('success' => false, 'message' => 'Email validation failed, please enter a valid email address.'));
        }

        echo $jResponse;
    }
}