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
            Mage::log($params['id']);

            $quote = Mage::getModel('sales/quote')->load($params['id']);
            $quote->setEbizmartsAbandonedcartFlag(1);
            $quote->save();
            $this->_getSession()->setQuoteId($quote->getId());
        }
        $this->_redirect('checkout/cart');
    }
}