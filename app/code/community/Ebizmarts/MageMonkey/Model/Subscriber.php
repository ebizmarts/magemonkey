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
        $store = Mage::helper('monkey')->getThisStore();
        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
            return $this;
        } else {
            return parent::sendUnsubscriptionEmail();
        }
    }

    public function sendConfirmationRequestEmail()
    {
        $store = Mage::helper('monkey')->getThisStore();
        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
            return $this;
        } else {
            return parent::sendConfirmationRequestEmail();
        }
    }

    public function sendConfirmationSuccessEmail()
    {
        $store = Mage::helper('monkey')->getThisStore();
        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) == 1 && !Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $store)) {
            return $this;
        } else {
            return parent::sendConfirmationSuccessEmail();
        }
    }

    public function confirm($code)
    {
        $store = Mage::helper('monkey')->getThisStore();
        if (Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_ACTIVE, $store) && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_CONFIRMATION_FLAG, $this->getStoreId()) && Mage::getStoreConfig(Ebizmarts_MageMonkey_Model_Config::GENERAL_CONFIRMATION_EMAIL, $this->getStoreId())){
            Mage::helper('monkey')->listsSubscription($this, 0);
        }
        if($this->getCode()==$code) {
            $this->setStatus(self::STATUS_SUBSCRIBED)
                ->setIsStatusChanged(true)
                ->save();
            return true;
        }

        return false;
        //parent::confirm($code);
    }
}