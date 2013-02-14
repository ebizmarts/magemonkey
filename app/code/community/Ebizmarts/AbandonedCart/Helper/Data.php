<?php

class Ebizmarts_AbandonedCart_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @return string
     */
    public function getVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Ebizmarts_AbandonedCart/version');
    }


    /**
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '30d'  => $this->__('Last 30 Days'),
            '60d'  => $this->__('Last 60 Days'),
            '90d'  => $this->__('Last 90 Days'),
            'lifetime' => $this->__('Lifetime'),
        );
    }

}