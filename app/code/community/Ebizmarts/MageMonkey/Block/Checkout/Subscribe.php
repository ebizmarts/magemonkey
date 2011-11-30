<?php

class Ebizmarts_MageMonkey_Block_Checkout_Subscribe extends Mage_Core_Block_Template
{

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
    	$alreadySubscribed = Mage::getModel('newsletter/subscriber')
							->loadByEmail($this->getQuote()->getCustomerEmail())
							->isSubscribed();

        if ( !$this->helper('monkey')->canCheckoutSubscribe() OR
              $alreadySubscribed ) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getQuote()
    {
    	return Mage::getSingleton('checkout/session')
    			->getQuote();
    }

}