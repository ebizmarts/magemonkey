<?php

/**
 * Subscriber status config source options model
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_MageMonkey
 * @author     Ebizmarts Team <info@ebizmarts.com>
 */
class Ebizmarts_MageMonkey_Model_System_Config_Source_OrderStatus
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
       $status = Mage::GetResourceModel('sales/order_status_collection')->toOptionArray();
       $allStateOptions = array('value' => 'all_status', 'label' => Mage::helper('monkey')->__('All Status'));
	   array_unshift($status, $allStateOptions);
       return $status;

    }

}