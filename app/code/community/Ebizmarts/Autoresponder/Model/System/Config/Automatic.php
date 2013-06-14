<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 6/14/13
 * Time   : 4:15 PM
 * File   : Automatic.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_Autoresponder_Model_System_Config_Automatic
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value'=> 1, 'label' => Mage::helper('ebizmarts_autoresponder')->__('Specific')),
            array('value'=> 2, 'label' => Mage::helper('ebizmarts_autoresponder')->__('Automatic'))
        );
        return $options;
    }
}