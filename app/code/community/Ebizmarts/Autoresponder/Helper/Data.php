<?php
/**
 * Author : Ebizmarts <info@ebizmarts.com>
 * Date   : 4/28/13
 * Time   : 11:20 AM
 * File   : Data.php
 * Module : Ebizmarts_Magemonkey
 */ 
class Ebizmarts_Autoresponder_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getLists()
    {
        $types = array();
        $storeId = Mage::app()->getStore()->getStoreId();
        $lists = Mage::getConfig()->getNode('default/ebizmarts_autoresponder')->asArray();
        $lists['abandonedcart'] = array('listname'=>'Abandoned Carts List');
        foreach ($lists as $key =>$data) {
            if(isset($data['listname'])) {
                if(Mage::getStoreConfig("ebizmarts_autoresponder/$key/active",$storeId)||($key=='abandonedcart'&&Mage::getStoreConfig("ebizmarts_abandonedcart/general/active",$storeId))) {
                    $types[$key]['listname'] = (string)$data['listname'];
                    $collection = Mage::getModel('ebizmarts_autoresponder/unsubscribe')->getCollection();
                    $email = $this->_getEmail();
                    $collection->addFieldToFilter('main_table.email',array('eq'=>$email))
                        ->addFieldToFilter('main_table.list',array('eq'=>$key))
                        ->addFieldToFilter('main_table.store_id',array('eq'=>$storeId));
                    if($collection->getSize() > 0) {
                        $types[$key]['checked'] = "";
                    }
                    else {
                        $types[$key]['checked'] = "checked";
                    }
                }
            }
        }
        return $types;
    }
    protected function _getEmail()
    {
        return Mage::helper('customer')->getCustomer()->getEmail();
    }
}