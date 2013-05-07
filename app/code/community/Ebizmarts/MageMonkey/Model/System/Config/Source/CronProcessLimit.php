<?php


/**
 * Cron Process available count limits options source
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_CronProcessLimit
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 100, 'label' => Mage::helper('monkey')->__('100')),
            array('value' => 200, 'label' => Mage::helper('monkey')->__('200')),
            array('value' => 500, 'label' => Mage::helper('monkey')->__('500')),
            array('value' => 1000, 'label' => Mage::helper('monkey')->__('1000')),
            array('value' => 5000, 'label' => Mage::helper('monkey')->__('5000')),
            array('value' => 10000, 'label' => Mage::helper('monkey')->__('10000')),
            array('value' => 20000, 'label' => Mage::helper('monkey')->__('20000'))
        );
    }
}