<?php
/**
 * Created by Ebizmarts
 * User: gonzalo@ebizmarts.com
 * Date: 3/20/13
 * Time: 4:07 PM
 */
class Ebizmarts_AbandonedCart_Model_System_Config_Automatic
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value'=> 1, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Specific')),
            array('value'=> 2, 'label' => Mage::helper('ebizmarts_abandonedcart')->__('Automatic'))
        );
        return $options;
    }
}