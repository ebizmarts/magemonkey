<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 5/3/13
 * Time   : 12:47 PM
 * File   : Customergroup.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Model_System_Config_Customergroup
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')
                ->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}
