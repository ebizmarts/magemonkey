<?php

class Ebizmarts_MageMonkey_Model_System_Config_Source_BulksyncDatasource
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
        	array('value' => 'customer', 'label' => Mage::helper('monkey')->__('Subscribe Magento customers to MailChimp')),
            array('value' => 'newsletter_subscriber', 'label' => Mage::helper('monkey')->__('Send Magento Newsletter subscribers to MailChimp')),
        );
    }

}