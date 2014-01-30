<?php

/**
 * MC source class for account data
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_MageMonkey_Model_System_Config_Source_Account
{

	/**
	 * Account details storage
	 *
	 * @access protected
	 * @var bool|array
	 */
    protected $_account_details = FALSE;

	/**
	 * Set AccountDetails on class property if not already set
	 *
	 * @return void
	 */
    public function __construct()
    {
        if (!$this->_account_details) {
            $this->_account_details = Mage::getSingleton('monkey/api')
                    ->getAccountDetails();
        }
    }

	/**
	 * Return data if API key is entered
	 *
	 * @return array
	 */
    public function toOptionArray()
    {
        if(is_array($this->_account_details)){
            return array(
                array('value' => 0, 'label' => Mage::helper('monkey')->__('Username:') . ' ' . $this->_account_details['username']),
                array('value' => 1, 'label' => Mage::helper('monkey')->__('Plan type:') . ' ' . $this->_account_details['plan_type']),
                array('value' => 2, 'label' => Mage::helper('monkey')->__('Is in trial mode?:') . ' ' . ($this->_account_details['is_trial'] ? Mage::helper('monkey')->__('Yes') : Mage::helper('monkey')->__('No')))
            );
        }else{
            return array(array('value' => '', 'label' => Mage::helper('monkey')->__('--- Enter your API KEY first ---')));
        }
    }

}
