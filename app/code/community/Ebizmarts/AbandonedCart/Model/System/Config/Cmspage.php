<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 8/13/13
 * Time   : 2:00 PM
 * File   : Cmspage.php
 * Module : Ebizmarts_Magemonkey
 */
class Ebizmarts_AbandonedCart_Model_System_Config_Cmspage
{

    public function toOptionArray()
    {
        $collection = Mage::getModel('cms/page')->getCollection()->addOrder('title', 'asc');
        return array('checkout/cart'=> "Shopping Cart (default page)") + $collection->toOptionIdArray();
    }

}
