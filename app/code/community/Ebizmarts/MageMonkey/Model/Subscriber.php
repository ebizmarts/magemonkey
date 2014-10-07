<?php
/**
 * Created by PhpStorm.
 * User: santisp
 * Date: 25/09/14
 * Time: 12:26 PM
 */

class Ebizmarts_MageMonkey_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{
    public function sendUnsubscriptionEmail()
    {
        if(Mage::getStoreConfig('monkey/general/active', Mage::helper('monkey')->getThisStore()) == 1) {
            return $this;
        }else{
            return parent::sendUnsubscriptionEmail();
        }
    }

    public function sendConfirmationRequestEmail()
    {
        if(Mage::getStoreConfig('monkey/general/active', Mage::helper('monkey')->getThisStore()) == 1) {
            return $this;
        }else{
            return parent::sendConfirmationRequestEmail();
        }
    }

    public function sendConfirmationSuccessEmail()
    {
        if(Mage::getStoreConfig('monkey/general/active', Mage::helper('monkey')->getThisStore()) == 1) {
            return $this;
        }else{
            return parent::sendConfirmationSuccessEmail();
        }
    }
}