<?php

/**
 * Checkout subscribe checkbox block renderer
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Block_Checkout_Subscribe extends Ebizmarts_MageMonkey_Block_Lists
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

	/**
	 * Retrieve current quote object from session
	 *
	 * @return Mage_Sales_Model_Quote
	 */
    public function getQuote()
    {
    	return Mage::getSingleton('checkout/session')
    			->getQuote();
    }

	/**
	 * Retrieve from config the status of the checkbox
	 *
	 * @see Ebizmarts_MageMonkey_Model_System_Config_Source_Checkoutsubscribe
	 * @return integer Config value possible values are 0,1,2,3
	 */
	public function checkStatus()
	{
		return (int)$this->helper('monkey')->config('checkout_subscribe');
	}

}
