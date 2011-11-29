<?php

class Ebizmarts_MageMonkey_Model_System_Config_Source_Account
{

    protected $_account_details = FALSE;

    public function __construct() {
        if (!$this->_account_details) {
            $this->_account_details = Mage::getSingleton('monkey/api')
                    ->getAccountDetails();
        }
    }

    public function toOptionArray() {
        if($this->_account_details){
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