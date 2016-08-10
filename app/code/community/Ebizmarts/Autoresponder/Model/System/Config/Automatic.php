<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Autoresponder
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Ebizmarts_Autoresponder_Model_System_Config_Automatic
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value' => 1, 'label' => Mage::helper('ebizmarts_autoresponder')->__('Specific')),
            array('value' => 2, 'label' => Mage::helper('ebizmarts_autoresponder')->__('Automatic'))
        );
        return $options;
    }
}