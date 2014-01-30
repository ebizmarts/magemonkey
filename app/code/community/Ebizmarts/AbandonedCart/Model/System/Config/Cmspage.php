<?php

/**
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_AbandonedCart
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_AbandonedCart_Model_System_Config_Cmspage
{

    public function toOptionArray()
    {
        $collection = Mage::getModel('cms/page')->getCollection()->addOrder('title', 'asc');
        return array('checkout/cart'=> "Shopping Cart (default page)") + $collection->toOptionIdArray();
    }

}
