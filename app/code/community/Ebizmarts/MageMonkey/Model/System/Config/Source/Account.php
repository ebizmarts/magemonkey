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
                array('value' => 0, 'label' => 'Username: ' . $this->_account_details['username']),
                array('value' => 1, 'label' => 'Plan type: ' . $this->_account_details['plan_type'])
            );
        }else{
            return array(array('value' => '', 'label' => Mage::helper('monkey')->__('--- Enter your API KEY first ---')));
        }
    }



}