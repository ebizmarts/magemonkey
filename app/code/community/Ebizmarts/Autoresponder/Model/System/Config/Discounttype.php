<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/14/13
 * Time   : 4:16 PM
 * File   : Discounttype.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_System_Config_Discounttype
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