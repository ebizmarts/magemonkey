<?php

class Ebizmarts_AbandonedCart_Model_System_Config_Discounttype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
          array('value'=> 1, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Fixed amount')),
          array('value'=> 2, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Percentage'))
        );
        return $options;
    }
}